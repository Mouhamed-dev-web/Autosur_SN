<?php

namespace App\Filament\Admin\Resources\Transactions;

use App\Enums\PaymentStatus;
use App\Filament\Admin\Resources\Transactions\Pages\ManageTransactions;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Finances';

    protected static ?string $navigationLabel = 'Transactions';

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
                TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('reference')->label('Référence')->searchable()->copyable(),
                TextColumn::make('user.name')->label('Client')->searchable(),
                TextColumn::make('provider')->label('Opérateur')->formatStateUsing(fn ($state) => $state->label()),
                TextColumn::make('gateway')->label('Passerelle')->badge(),
                TextColumn::make('amount')->label('Montant')->numeric(thousandsSeparator: ' ')->suffix(' FCFA')->sortable(),
                TextColumn::make('status')->label('Statut')->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => match ($state) {
                        PaymentStatus::PAID => 'success',
                        PaymentStatus::PENDING => 'warning',
                        PaymentStatus::FAILED => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->label('Statut')->options(
                    collect(PaymentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()
                ),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTransactions::route('/')];
    }
}