<?php

namespace App\Filament\Admin\Resources\Vehicles;

use App\Filament\Admin\Resources\Vehicles\Pages\ManageVehicles;
use App\Models\Vehicle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Utilisateurs';

    protected static ?string $navigationLabel = 'Véhicules';

    protected static ?string $modelLabel = 'véhicule';

    protected static ?string $pluralModelLabel = 'véhicules';

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
                TextColumn::make('plate')->label('Immatriculation')->searchable()->sortable(),
                TextColumn::make('brand')->label('Marque')->searchable(),
                TextColumn::make('model')->label('Modèle')->searchable(),
                TextColumn::make('year')->label('Année')->sortable(),
                TextColumn::make('user.name')->label('Propriétaire')->searchable(),
                TextColumn::make('insurance_status')->label('Assurance')->badge()->formatStateUsing(fn ($state) => $state->label()),
                TextColumn::make('insurance_expires_at')->label('Fin d\'assurance')->date('d/m/Y')->sortable(),
                TextColumn::make('technical_visit_expires_at')->label('Prochaine visite')->date('d/m/Y')->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageVehicles::route('/')];
    }
}