<?php

namespace App\Services\Payments;

use App\Models\Disbursement;
use App\Models\MerchantApp;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Pays out collected money: a disbursement takes every paid payment of an app
 * that is not settled yet (optionally only those paid up to a date) and
 * records one payout for their net total.
 */
class DisbursementService
{
    /** @return array{count: int, amount: float} */
    public function preview(MerchantApp $app, ?string $until = null): array
    {
        $query = $this->settleable($app, $until);

        return ['count' => (clone $query)->count(), 'amount' => round((float) $query->sum('net_amount'), 2)];
    }

    /**
     * @param  array{method: string, status?: string, until?: string|null, account_details?: string|null, transaction_reference?: string|null, note?: string|null}  $data
     */
    public function create(MerchantApp $app, array $data, ?int $userId = null): Disbursement
    {
        return DB::transaction(function () use ($app, $data, $userId) {
            $payments = $this->settleable($app, $data['until'] ?? null)->lockForUpdate()->get(['id', 'net_amount']);

            if ($payments->isEmpty()) {
                throw new PaymentException('Nothing to disburse: this app has no paid payments that are not settled yet.');
            }

            $completed = ($data['status'] ?? 'completed') === 'completed';

            $disbursement = Disbursement::create([
                'merchant_app_id' => $app->id,
                'amount' => round((float) $payments->sum('net_amount'), 2),
                'payments_count' => $payments->count(),
                'method' => $data['method'],
                'account_details' => $data['account_details'] ?? $app->payout_details,
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'note' => $data['note'] ?? null,
                'status' => $completed ? 'completed' : 'pending',
                'disbursed_at' => $completed ? now() : null,
                'created_by' => $userId,
            ]);

            Payment::whereKey($payments->pluck('id'))->update(['disbursement_id' => $disbursement->id]);

            return $disbursement;
        });
    }

    public function complete(Disbursement $disbursement, ?string $transactionReference = null): void
    {
        $disbursement->update([
            'status' => 'completed',
            'disbursed_at' => now(),
            'transaction_reference' => $transactionReference ?: $disbursement->transaction_reference,
        ]);
    }

    /** Undo: its payments become unsettled again. */
    public function cancel(Disbursement $disbursement): void
    {
        DB::transaction(function () use ($disbursement) {
            $disbursement->payments()->update(['disbursement_id' => null]);
            $disbursement->update(['status' => 'cancelled']);
        });
    }

    protected function settleable(MerchantApp $app, ?string $until)
    {
        return $app->payments()->unsettled()
            ->when($until, fn ($q) => $q->where('paid_at', '<=', Carbon::parse($until)->endOfDay()));
    }
}
