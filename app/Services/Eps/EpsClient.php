<?php

namespace App\Services\Eps;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * EPS (Easy Payment System) merchant API, per "EPS Merchant API Integration Guide v0.4".
 *
 *  1. GetToken   POST /v1/Auth/GetToken                       x-hash = hash(userName)
 *  2. Initialize POST /v1/EPSEngine/InitializeEPS             x-hash = hash(merchantTransactionId) + Bearer
 *  3. Verify     GET  /v1/EPSEngine/CheckMerchantTransactionStatus?merchantTransactionId=…
 *                                                             x-hash = hash(merchantTransactionId) + Bearer
 *
 * hash(v) = base64( HMAC-SHA512( key: UTF-8 bytes of the Hash Key, data: v ) )
 */
class EpsClient
{
    public function __construct(protected array $config) {}

    public static function make(): self
    {
        return new self(config('payment.eps'));
    }

    public function isConfigured(): bool
    {
        foreach (['merchant_id', 'store_id', 'username', 'password', 'hash_key'] as $key) {
            if (blank($this->config[$key] ?? null)) {
                return false;
            }
        }

        return filled($this->baseUrl());
    }

    public function isSandbox(): bool
    {
        return (bool) ($this->config['sandbox'] ?? true);
    }

    public function baseUrl(): ?string
    {
        $url = $this->config['base_url'] ?? null;

        if (blank($url) && $this->isSandbox()) {
            $url = $this->config['sandbox_url'] ?? null;
        }

        return $url ? rtrim($url, '/') : null;
    }

    public function merchantId(): string
    {
        return (string) $this->config['merchant_id'];
    }

    public function storeId(): string
    {
        return (string) $this->config['store_id'];
    }

    public function transactionTypeId(): int
    {
        return (int) ($this->config['transaction_type_id'] ?? 1);
    }

    public function hash(string $value): string
    {
        return base64_encode(hash_hmac('sha512', $value, (string) $this->config['hash_key'], true));
    }

    /** Bearer token, cached until shortly before it expires. */
    public function token(bool $fresh = false): string
    {
        $cacheKey = 'eps.token.'.md5($this->baseUrl().'|'.$this->config['username']);

        if ($fresh) {
            Cache::forget($cacheKey);
        }

        if ($token = Cache::get($cacheKey)) {
            return $token;
        }

        $username = (string) $this->config['username'];

        $response = $this->send(fn () => $this->http()
            ->withHeaders(['x-hash' => $this->hash($username)])
            ->post($this->baseUrl().'/v1/Auth/GetToken', [
                'userName' => $username,
                'password' => (string) $this->config['password'],
            ]), 'GetToken');

        $data = $this->decode($response);
        $token = self::value($data, 'token');

        if (blank($token)) {
            throw new EpsException('EPS login failed: '.$this->errorText($data, $response), $data);
        }

        Cache::put($cacheKey, $token, $this->tokenTtl($token));

        return $token;
    }

    /**
     * Start a payment. Returns ['transaction_id', 'redirect_url', 'raw'].
     *
     * @param  array<string, mixed>  $payload  InitializeEPS body (merchantId/storeId are filled in)
     */
    public function initialize(array $payload): array
    {
        $payload = ['merchantId' => $this->merchantId(), 'storeId' => $this->storeId()] + $payload;
        $mtid = (string) $payload['merchantTransactionId'];

        $data = $this->authorized('InitializeEPS', fn (string $token) => $this->http()
            ->withToken($token)
            ->withHeaders(['x-hash' => $this->hash($mtid)])
            ->post($this->baseUrl().'/v1/EPSEngine/InitializeEPS', $payload));

        $redirect = self::value($data, 'RedirectURL');

        if (blank($redirect)) {
            throw new EpsException('EPS could not start the payment: '.$this->errorText($data), $data);
        }

        return [
            'transaction_id' => self::value($data, 'TransactionId'),
            'redirect_url' => $redirect,
            'raw' => $data,
        ];
    }

    /**
     * Ask EPS what happened to a transaction. Returns the decoded EPS response.
     * Key fields: Status ("Success" …), TotalAmount, FinancialEntity, MerchantTransactionId.
     */
    public function status(string $merchantTransactionId): array
    {
        return $this->authorized('CheckMerchantTransactionStatus', fn (string $token) => $this->http()
            ->withToken($token)
            ->withHeaders(['x-hash' => $this->hash($merchantTransactionId)])
            ->get($this->baseUrl().'/v1/EPSEngine/CheckMerchantTransactionStatus', [
                'merchantTransactionId' => $merchantTransactionId,
            ]));
    }

    /** Case-insensitive read: EPS mixes "token", "RedirectURL", "Status" … */
    public static function value(array $data, string $key): mixed
    {
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        foreach ($data as $k => $v) {
            if (is_string($k) && strcasecmp($k, $key) === 0) {
                return $v;
            }
        }

        return null;
    }

    protected function authorized(string $operation, callable $call): array
    {
        $response = $this->send(fn () => $call($this->token()), $operation);

        // Token expired early or was revoked: log in again once.
        if ($response->status() === 401) {
            $response = $this->send(fn () => $call($this->token(fresh: true)), $operation);
        }

        $data = $this->decode($response);

        if ($response->failed()) {
            throw new EpsException("EPS {$operation} failed: ".$this->errorText($data, $response), $data);
        }

        return $data;
    }

    protected function send(callable $request, string $operation): Response
    {
        $this->ensureConfigured();

        try {
            return $request();
        } catch (ConnectionException $e) {
            Log::warning("EPS {$operation}: connection failed", ['error' => $e->getMessage()]);

            throw new EpsException('Could not reach the payment gateway. Please try again in a moment.');
        }
    }

    protected function http(): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->timeout((int) ($this->config['timeout'] ?? 20))
            ->connectTimeout(8);
    }

    protected function decode(Response $response): array
    {
        $data = $response->json();

        return is_array($data) ? $data : ['body' => mb_substr((string) $response->body(), 0, 1000), 'http_status' => $response->status()];
    }

    protected function errorText(array $data, ?Response $response = null): string
    {
        $message = self::value($data, 'ErrorMessage') ?: self::value($data, 'message') ?: self::value($data, 'title');
        $code = self::value($data, 'ErrorCode');

        if (blank($message) && $response) {
            $message = 'HTTP '.$response->status();
        }

        return trim(($message ?: 'unknown error').($code ? " (code {$code})" : ''));
    }

    protected function ensureConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new EpsException('EPS is not configured. Set EPS_MERCHANT_ID, EPS_STORE_ID, EPS_USERNAME, EPS_PASSWORD, EPS_HASH_KEY'
                .($this->isSandbox() ? '' : ' and EPS_BASE_URL').' in .env.');
        }

        // EPS_BASE_URL pointing at this website makes it call itself: on a
        // single-worker server that request waits forever.
        $epsHost = $this->hostOf($this->baseUrl());
        $ownHosts = array_filter([$this->hostOf(config('app.url')), app()->runningInConsole() ? null : $this->hostOf(request()->getSchemeAndHttpHost())]);

        if ($epsHost && in_array($epsHost, $ownHosts, true)) {
            throw new EpsException('EPS_BASE_URL points to this website ('.$this->baseUrl().'). Leave it empty for the sandbox, or set the live EPS API URL.');
        }
    }

    protected function hostOf(?string $url): ?string
    {
        $parts = $url ? parse_url($url) : null;

        if (empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']) === 'localhost' ? '127.0.0.1' : strtolower($parts['host']);

        return $host.':'.($parts['port'] ?? (($parts['scheme'] ?? 'http') === 'https' ? 443 : 80));
    }

    /** Seconds to cache the JWT: until 2 minutes before its "exp", 50 minutes if unreadable. */
    protected function tokenTtl(string $token): int
    {
        $parts = explode('.', $token);
        $claims = isset($parts[1]) ? json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true) : null;
        $exp = is_array($claims) ? (int) ($claims['exp'] ?? 0) : 0;

        if ($exp > 0) {
            return max(60, $exp - time() - 120);
        }

        return 3000;
    }
}
