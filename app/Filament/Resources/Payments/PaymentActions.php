<?php

namespace App\Filament\Resources\Payments;

use App\Models\Payment;
use App\Services\Eps\EpsException;
use App\Services\Payments\PaymentService;
use App\Services\Payments\WebhookNotifier;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

/** Admin actions shared by the payments table and the payment page. */
class PaymentActions
{
    /** Ask EPS again about the newest attempt (useful while "processing"). */
    public static function verify(): Action
    {
        return Action::make('verifyWithEps')
            ->label('Check with EPS')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->visible(fn (Payment $record): bool => ! $record->isPaid() && $record->attempts()->whereNotNull('redirect_url')->exists())
            ->action(function (Payment $record): void {
                $attempt = $record->attempts()->whereNotNull('redirect_url')->first();

                try {
                    $payment = app(PaymentService::class)->verify($attempt);
                } catch (EpsException $e) {
                    Notification::make()->title('EPS did not answer')->body($e->getMessage())->danger()->send();

                    return;
                }

                Notification::make()
                    ->title('Status: '.(Payment::STATUSES[$payment->status] ?? $payment->status))
                    ->body('EPS says: '.($attempt->fresh()->gateway_status ?: 'no status yet'))
                    ->color(Payment::STATUS_COLORS[$payment->status] ?? 'gray')
                    ->send();
            });
    }

    public static function resendWebhook(): Action
    {
        return Action::make('resendWebhook')
            ->label('Resend webhook')
            ->icon('heroicon-o-paper-airplane')
            ->color('gray')
            ->requiresConfirmation()
            ->modalDescription('Sends the current status of this payment to the app again.')
            ->visible(fn (Payment $record): bool => filled(app(WebhookNotifier::class)->url($record)) && $record->status !== 'pending')
            ->action(function (Payment $record): void {
                $record->forceFill(['webhook_attempts' => 0])->save();
                $ok = app(WebhookNotifier::class)->send($record);

                Notification::make()
                    ->title($ok ? 'Webhook delivered' : 'Webhook failed')
                    ->body($record->fresh()->webhook_last_response)
                    ->color($ok ? 'success' : 'danger')
                    ->send();
            });
    }
}
