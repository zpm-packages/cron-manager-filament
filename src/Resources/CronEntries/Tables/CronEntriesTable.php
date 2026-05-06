<?php

namespace ZPMPackages\FilamentCronManager\Resources\CronEntries\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use ZPMPackages\CronManager\CronExpressionParser;
use ZPMPackages\LaravelCronManager\Models\CronEntry;

class CronEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->columns([
                TextColumn::make('position')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('schedule')
                    ->state(fn (CronEntry $record): string => self::getScheduleLabel($record->schedule))
                    ->description(fn (CronEntry $record): ?string => self::getScheduleDescription($record->schedule))
                    ->wrap()
                    ->extraAttributes(['style' => 'max-width: 400px; white-space: normal;'])
                    ->searchable(),
                TextColumn::make('command')
                    ->limit(40),
                TextColumn::make('comment')
                    ->limit(40)
                    ->placeholder('No comment'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    private static function getHumanReadableSchedule(string $schedule): ?string
    {
        $humanReadableSchedule = CronExpressionParser::toHumanReadable($schedule);

        if ($humanReadableSchedule === $schedule) {
            return null;
        }

        return ucfirst($humanReadableSchedule);
    }

    private static function getScheduleLabel(string $schedule): string
    {
        $humanReadableSchedule = self::getHumanReadableSchedule($schedule);

        if ($humanReadableSchedule !== null) {
            return $humanReadableSchedule;
        }

        return match (true) {
            $schedule === 'Multiple triggers defined' => 'Runs on multiple schedules',
            $schedule === 'Event trigger' => 'Runs on a system event',
            $schedule === 'At startup' => 'Runs at system startup',
            $schedule === 'Unsupported trigger' => 'Uses an unsupported trigger',
            str_starts_with($schedule, 'At log on') => 'Runs ' . lcfirst($schedule),
            str_starts_with($schedule, 'Once at ') => 'Runs ' . lcfirst($schedule),
            default => $schedule,
        };
    }

    private static function getScheduleDescription(string $schedule): ?string
    {
        return CronExpressionParser::isValidCronExpression($schedule) ? $schedule : null;
    }
}