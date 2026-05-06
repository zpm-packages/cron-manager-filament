<?php

namespace ZPMPackages\FilamentCronManager\Resources\CronEntries\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use ZPMPackages\FilamentCronManager\Resources\CronEntries\CronEntryResource;
use ZPMPackages\LaravelCronManager\Services\DatabaseSyncedCronCrudService;

class EditCronEntry extends EditRecord
{
    protected static string $resource = CronEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        app(DatabaseSyncedCronCrudService::class)->syncSystemFromDatabase();
    }

    protected function afterDelete(): void
    {
        app(DatabaseSyncedCronCrudService::class)->syncSystemFromDatabase();
    }
}