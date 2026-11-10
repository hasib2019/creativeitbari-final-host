<?php

namespace App\Filament\Resources\TeamMembers;

use App\Filament\Resources\TeamMembers\Pages\ManageTeamMembers;
use App\Filament\Support\Fields;
use App\Models\TeamMember;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Team';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('designation')->maxLength(255)->placeholder('e.g. Lead Developer'),
                Textarea::make('bio')->rows(3)->columnSpanFull(),
                Fields::image('photo', 'team')->avatar()->columnSpanFull(),
                Fieldset::make('Social links')
                    ->schema([
                        TextInput::make('social_links.facebook')->label('Facebook')->url()->prefixIcon('heroicon-o-link'),
                        TextInput::make('social_links.linkedin')->label('LinkedIn')->url()->prefixIcon('heroicon-o-link'),
                        TextInput::make('social_links.twitter')->label('X / Twitter')->url()->prefixIcon('heroicon-o-link'),
                        TextInput::make('social_links.github')->label('GitHub')->url()->prefixIcon('heroicon-o-link'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                ...Fields::visibility(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Fields::imageColumn('photo', circular: true),
                TextColumn::make('name')->searchable()->sortable()->weight('medium'),
                TextColumn::make('designation')->searchable()->color('gray'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTeamMembers::route('/'),
        ];
    }
}
