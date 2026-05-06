<?php

namespace ZPMPackages\FilamentCronManager;

use Illuminate\Support\ServiceProvider;

class FilamentCronManagerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-cron-manager');
    }
}