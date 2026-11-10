<?php

namespace App\Filament\Admin\Resources\InsuranceOffers\Pages;

use App\Filament\Admin\Resources\InsuranceOffers\InsuranceOfferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageInsuranceOffers extends ManageRecords
{
    protected static string $resource = InsuranceOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
