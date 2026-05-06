<?php

namespace ZPMPackages\FilamentCronManager\Resources\CronEntries\Pages;

use Filament\Resources\Pages\CreateRecord;
use ZPMPackages\FilamentCronManager\Resources\CronEntries\CronEntryResource;
use ZPMPackages\LaravelCronManager\Services\DatabaseSyncedCronCrudService;

class CreateCronEntry extends CreateRecord
{
    protected static string $resource = CronEntryResource::class;

    protected function afterCreate(): void
    {
        app(DatabaseSyncedCronCrudService::class)->syncSystemFromDatabase();
    }
}