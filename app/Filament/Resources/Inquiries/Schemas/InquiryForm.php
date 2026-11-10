<?php

namespace App\Filament\Resources\Inquiries\Schemas;

use App\Models\Inquiry;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InquiryForm
{
    /** Only status and admin_note are editable; everything else is shown read-only. */
    public static function configure(Schema $schema): Schema
    {
        $readOnly = fn (string $name, ?string $label = null): TextInput => TextInput::make($name)
            ->label($label)
            ->disabled()
            ->dehydrated(false);

        return $schema
            ->columns(3)
            ->components([
                Section::make('Follow-up')
                    ->schema([
                        Select::make('status')
                            ->options(Inquiry::STATUSES)
                            ->required()
                            ->native(false),
                        Textarea::make('admin_note')
                            ->label('Internal note')
                            ->rows(6)
                            ->helperText('Only visible to admins.'),
                    ])
                    ->columnSpan(['lg' => 1]),
                Section::make('Inquiry (read-only)')
                    ->schema([
                        Select::make('type')->options(Inquiry::TYPES)->disabled()->dehydrated(false),
                        $readOnly('name'),
                        $readOnly('email'),
                        $readOnly('phone'),
                        $readOnly('company'),
                        $readOnly('item_name', 'Product / Item'),
                        $readOnly('subject'),
                        $readOnly('budget'),
                        Textarea::make('message')->rows(5)->disabled()->dehydrated(false)->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 2]),
            ]);
    }
}
