<?php

namespace App\Filament\Admin\Resources\AuditLogs;

use App\Filament\Admin\Resources\AuditLogs\Pages\ManageAuditLogs;
use App\Models\AuditLog;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Journal d\'audit';

    protected static ?string $modelLabel = 'action';

    protected static ?string $pluralModelLabel = 'journal d\'audit';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
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
                TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('user.name')->label('Auteur')->placeholder('Système')->searchable(),
                TextColumn::make('action')->label('Action')->badge()->searchable(),
                TextColumn::make('subject_type')->label('Objet')
                    ->formatStateUsing(fn ($state, AuditLog $record) => $state ? $state.' #'.$record->subject_id : '—'),
                TextColumn::make('details')->label('Détails')
                    ->getStateUsing(fn (AuditLog $record) => $record->details ? json_encode($record->details, JSON_UNESCAPED_UNICODE) : null)
                    ->wrap()->limit(150),
                TextColumn::make('ip')->label('IP'),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageAuditLogs::route('/')];
    }
}