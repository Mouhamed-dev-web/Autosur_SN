<?php

namespace App\Filament\Admin\Resources\ContactMessages;

use App\Filament\Admin\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static string|\UnitEnum|null $navigationGroup = 'Support';

    protected static ?string $navigationLabel = 'Messages de contact';

    protected static ?string $modelLabel = 'message';

    protected static ?string $pluralModelLabel = 'messages de contact';

    public static function getNavigationBadge(): ?string
    {
        $open = ContactMessage::whereNull('handled_at')->count();

        return $open > 0 ? (string) $open : null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Reçu le')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('name')->label('Nom')->searchable(),
                TextColumn::make('subject')->label('Sujet')->formatStateUsing(fn ($state) => ContactMessage::SUBJECTS[$state] ?? $state)->badge(),
                TextColumn::make('email')->label('E-mail')->searchable()->copyable(),
                TextColumn::make('phone')->label('Téléphone')->copyable(),
                TextColumn::make('message')->label('Message')->wrap()->limit(200),
                IconColumn::make('handled_at')->label('Traité')->boolean()
                    ->getStateUsing(fn (ContactMessage $record) => $record->handled_at !== null),
            ])
            ->filters([
                TernaryFilter::make('handled')->label('Traité')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('handled_at'),
                        false: fn ($query) => $query->whereNull('handled_at'),
                    ),
            ])
            ->recordActions([
                Action::make('handled')
                    ->label('Marquer traité')
                    ->icon('heroicon-o-check')
                    ->visible(fn (ContactMessage $record) => $record->handled_at === null)
                    ->action(fn (ContactMessage $record) => $record->update(['handled_at' => now()])),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageContactMessages::route('/')];
    }
}