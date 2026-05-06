<?php

namespace ZPMPackages\FilamentCronManager\Tests;

use Filament\FilamentServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use ZPMPackages\FilamentCronManager\FilamentCronManagerServiceProvider;
use ZPMPackages\LaravelCronManager\CronManagerServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,
            CronManagerServiceProvider::class,
            FilamentCronManagerServiceProvider::class,
        ];
    }
}