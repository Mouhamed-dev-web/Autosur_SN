<?php

namespace App\Filament\Admin\Resources\InsuranceOffers;

use App\Enums\CoverType;
use App\Filament\Admin\Resources\InsuranceOffers\Pages\ManageInsuranceOffers;
use App\Models\InsuranceOffer;
use App\Models\Partner;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InsuranceOfferResource extends Resource
{
    protected static ?string $model = InsuranceOffer::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Assurance';

    protected static ?string $navigationLabel = 'Offres d\'assurance';

    protected static ?string $modelLabel = 'offre';

    protected static ?string $pluralModelLabel = 'offres d\'assurance';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('partner_id')
                ->label('Assureur partenaire')
                ->options(fn () => Partner::where('type', 'assurance')->where('is_validated', true)->pluck('company_name', 'id'))
                ->searchable()
                ->live()
                ->afterStateUpdated(fn ($state, $set) => $set('company_name', Partner::find($state)?->company_name)),
            TextInput::make('company_name')->label('Nom affiché')->required()->maxLength(120),
            TextInput::make('name')->label('Nom de l\'offre')->required()->maxLength(120),
            Select::make('cover_type')->label('Couverture')->required()->options(
                collect(CoverType::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all()
            ),
            TextInput::make('base_premium')->label('Prime de base (FCFA / an)')->numeric()->required()->minValue(0),
            TextInput::make('per_cv_premium')->label('Supplément par CV (FCFA / an)')->numeric()->required()->minValue(0),
            TextInput::make('value_rate')->label('% de la valeur du véhicule / an')->numeric()->required()->minValue(0)->maxValue(20)->step(0.1),
            TextInput::make('commission_rate')->label('Commission AutoSur.sn (%)')->numeric()->required()
                ->minValue(5)->maxValue(12)->step(0.5)
                ->helperText('Entre 5 % et 12 %, comme prévu au cahier des charges.'),
            Textarea::make('description')->label('Description')->rows(2),
            Toggle::make('is_active')->label('Offre active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_name')->label('Assureur')->searchable()->sortable(),
                TextColumn::make('name')->label('Offre')->searchable(),
                TextColumn::make('cover_type')->label('Couverture')->badge()->formatStateUsing(fn ($state) => $state->label()),
                TextColumn::make('base_premium')->label('Prime de base')->numeric(thousandsSeparator: ' ')->suffix(' FCFA'),
                TextColumn::make('commission_rate')->label('Commission')->suffix(' %'),
                TextColumn::make('partner.company_name')->label('Compte assureur')->placeholder('Non rattachée'),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageInsuranceOffers::route('/')];
    }
}