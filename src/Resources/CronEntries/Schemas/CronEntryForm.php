<?php

namespace ZPMPackages\FilamentCronManager\Resources\CronEntries\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use ZPMPackages\CronManager\Support\ScheduleOptions;

class CronEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('schedule')
                ->required()
                ->placeholder('every hour or 0 * * * *')
                ->datalist(array_keys(ScheduleOptions::selectOptions()))
                ->helperText('Choose a suggested schedule or enter a custom cron expression. Monthly and yearly presets are Unix only.'),
            Textarea::make('command')
                ->required()
                ->rows(3)
                ->helperText(ScheduleOptions::commandTip('/schedule.php')),
            TextInput::make('comment'),
        ]);
    }
}