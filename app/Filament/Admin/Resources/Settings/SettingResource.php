<?php

namespace App\Filament\Admin\Resources\Settings;

use App\Filament\Admin\Resources\Settings\Pages\ManageSettings;
use App\Models\Setting;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Paramètres';

    protected static ?string $modelLabel = 'paramètre';

    protected static ?string $pluralModelLabel = 'paramètres';

    public static function canCreate(): bool
    {
        return false; // seuls les paramètres prévus par l'application existent
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label')->label('Paramètre')->disabled()->dehydrated(false),
            TextInput::make('value')
                ->label('Valeur')
                ->required()
                ->rules(fn (?Setting $record) => explode('|', Setting::RULES[$record?->name] ?? 'required|string|max:255')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Paramètre')->wrap(),
                TextColumn::make('value')->label('Valeur')->badge(),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSettings::route('/')];
    }
}