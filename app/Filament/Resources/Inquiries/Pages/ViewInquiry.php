<?php

namespace App\Filament\Resources\Inquiries\Pages;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Models\Inquiry;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInquiry extends ViewRecord
{
    protected static string $resource = InquiryResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Inquiry $inquiry */
        $inquiry = $this->getRecord();

        return [
            Action::make('reply')
                ->label('Reply by email')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->url('mailto:'.$inquiry->email.'?subject='.rawurlencode('Re: '.($inquiry->subject ?: $inquiry->item_name ?: 'Your inquiry'))),
            Action::make('whatsapp')
                ->label('WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->visible(filled($inquiry->phone))
                ->url('https://wa.me/'.preg_replace('/\D+/', '', (string) $inquiry->phone), shouldOpenInNewTab: true),
            EditAction::make()->label('Update status / note'),
        ];
    }
}
