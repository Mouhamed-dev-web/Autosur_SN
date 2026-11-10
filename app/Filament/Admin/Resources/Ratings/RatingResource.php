<?php

namespace App\Filament\Admin\Resources\Ratings;

use App\Filament\Admin\Resources\Ratings\Pages\ManageRatings;
use App\Models\Rating;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RatingResource extends Resource
{
    protected static ?string $model = Rating::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|\UnitEnum|null $navigationGroup = 'Partenaires';

    protected static ?string $navigationLabel = 'Avis';

    protected static ?string $modelLabel = 'avis';

    protected static ?string $pluralModelLabel = 'avis';

    protected static ?int $navigationSort = 2;

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
                TextColumn::make('partner.company_name')->label('Partenaire')->searchable(),
                TextColumn::make('user.name')->label('Client')->searchable(),
                TextColumn::make('score')->label('Note')->formatStateUsing(fn ($state) => str_repeat('★', (int) $state))->sortable(),
                TextColumn::make('comment')->label('Commentaire')->wrap()->limit(120),
                ToggleColumn::make('is_hidden')->label('Masqué'),
                TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_hidden')->label('Masqués'),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageRatings::route('/')];
    }
}