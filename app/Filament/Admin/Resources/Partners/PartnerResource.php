<?php

namespace App\Filament\Admin\Resources\Partners;

use App\Enums\PartnerType;
use App\Filament\Admin\Resources\Partners\Pages\ManagePartners;
use App\Models\Partner;
use App\Services\PartnerService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static string|\UnitEnum|null $navigationGroup = 'Partenaires';

    protected static ?string $navigationLabel = 'Partenaires';

    protected static ?string $modelLabel = 'partenaire';

    protected static ?string $pluralModelLabel = 'partenaires';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $pending = Partner::where('is_validated', false)->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function canCreate(): bool
    {
        return false; // les partenaires s'inscrivent eux-mêmes
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('company_name')->label('Raison sociale')->required()->maxLength(120),
            TextInput::make('contact_phone')->label('Téléphone professionnel')->required()->maxLength(20),
            TextInput::make('radius_km')->label('Rayon (km)')->numeric()->minValue(1)->maxValue(100),
            TextInput::make('base_lat')->label('Latitude')->numeric(),
            TextInput::make('base_lng')->label('Longitude')->numeric(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('company_name')->label('Raison sociale')->searchable()->sortable(),
                TextColumn::make('type')->label('Type')->badge()
                    ->formatStateUsing(fn ($state) => $state->label()),
                TextColumn::make('user.name')->label('Responsable')->searchable(),
                TextColumn::make('contact_phone')->label('Téléphone'),
                IconColumn::make('is_validated')->label('Validé')->boolean(),
                IconColumn::make('is_available')->label('Disponible')->boolean(),
                TextColumn::make('created_at')->label('Inscrit le')->dateTime('d/m/Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->label('Type')->options(
                    collect(PartnerType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])->all()
                ),
                TernaryFilter::make('is_validated')->label('Validé'),
            ])
            ->recordActions([
                Action::make('validate')
                    ->label('Valider')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Partner $record) => ! $record->is_validated)
                    ->action(function (Partner $record) {
                        app(PartnerService::class)->validate($record);
                        Notification::make()->title('Partenaire validé')->success()->send();
                    }),
                Action::make('suspend')
                    ->label('Suspendre')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Partner $record) => $record->is_validated)
                    ->action(function (Partner $record) {
                        app(PartnerService::class)->suspend($record);
                        Notification::make()->title('Partenaire suspendu')->warning()->send();
                    }),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePartners::route('/')];
    }
}