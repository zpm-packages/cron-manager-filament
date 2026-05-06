<?php

namespace ZPMPackages\FilamentCronManager;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Container\Container;
use ZPMPackages\FilamentCronManager\Pages\ManageSystemCronsPage;
use ZPMPackages\FilamentCronManager\Resources\CronEntries\CronEntryResource;

class FilamentCronManagerPlugin implements Plugin
{
    public static function make(): static
    {
        return Container::getInstance()->make(static::class);
    }

    public function getId(): string
    {
        return 'cron-manager';
    }

    public function register(Panel $panel): void
    {
        if ((bool) config('cron-manager.sync_with_database', false)) {
            $panel->resources([
                CronEntryResource::class,
            ]);

            return;
        }

        $panel->pages([
            ManageSystemCronsPage::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
    }
}