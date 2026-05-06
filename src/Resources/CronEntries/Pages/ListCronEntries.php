<?php

namespace ZPMPackages\FilamentCronManager\Resources\CronEntries\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use ZPMPackages\FilamentCronManager\Resources\CronEntries\CronEntryResource;

class ListCronEntries extends ListRecords
{
    protected static string $resource = CronEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}