<?php

namespace ZPMPackages\FilamentCronManager\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Support\ArrayRecord;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use ZPMPackages\CronManager\CronExpressionParser;
use ZPMPackages\CronManager\Support\ScheduleOptions;
use ZPMPackages\LaravelCronManager\Contracts\CronCrudService;

class ManageSystemCronsPage extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string | \UnitEnum | null $navigationGroup = 'System';

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Cron Manager';

    protected string $view = 'filament-cron-manager::pages.manage-system-crons-page';
    protected ?string $heading = 'Manage system crons';

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): LengthAwarePaginator => $this->getRecordsPaginator())
            ->modelLabel('cron')
            ->pluralModelLabel('crons')
            ->columns([
                TextColumn::make('schedule')
                    ->state(fn (array $record): string => $this->getScheduleLabel($record['schedule']))
                    ->description(fn (array $record): ?string => $this->getScheduleDescription($record['schedule']))
                    ->wrap()
                    ->extraAttributes(['style' => 'max-width: 400px; white-space: normal;'])
                    ->searchable(),
                TextColumn::make('command')
                    ->limit(40),
                TextColumn::make('comment')
                    ->limit(40)
                    ->placeholder('No comment'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->schema($this->formSchema())
                    ->action(function (array $data): void {
                        \app(CronCrudService::class)->create($data);
                        $this->flushCachedTableRecords();
                    }),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon(Heroicon::PencilSquare)
                    ->visible(fn (array $record): bool => $this->canEditSchedule($record['schedule']))
                    ->modalHeading('Edit cron')
                    ->schema($this->formSchema())
                    ->fillForm(fn (array $record): array => Arr::only($record, ['schedule', 'command', 'comment']))
                    ->action(function (array $data, array $record): void {
                        \app(CronCrudService::class)->update($record['identifier'], $data);
                        $this->flushCachedTableRecords();
                    }),
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete cron')
                    ->modalDescription('Are you sure you want to delete this cron entry?')
                    ->modalSubmitActionLabel('Delete')
                    ->action(function (array $record): void {
                        \app(CronCrudService::class)->delete($record['identifier']);
                        $this->flushCachedTableRecords();
                    }),
            ]);
    }

    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model | array $record): string
    {
        return is_array($record) ? $record['identifier'] : (string) $record->getKey();
    }

    /**
     * @return array<int, Select|TextInput|Textarea>
     */
    private function formSchema(): array
    {
        return [
            Select::make('schedule')
                ->required()
                ->options(ScheduleOptions::selectOptions())
                ->searchable()
                ->placeholder('Select a schedule')
                ->helperText('Choose a suggested schedule. Monthly and yearly presets are Unix only.'),
            Textarea::make('command')
                ->required()
                ->rows(3)
                ->helperText(ScheduleOptions::commandTip('/schedule.php')),
            TextInput::make('comment'),
        ];
    }

    private function getRecordsPaginator(): LengthAwarePaginator
    {
        $records = \app(CronCrudService::class)
            ->list()
            ->map(fn ($entry) => [
                ArrayRecord::getKeyName() => $entry->identifier,
                'identifier' => $entry->identifier,
                'schedule' => $entry->schedule,
                'command' => $entry->command,
                'comment' => $entry->comment,
            ])
            ->values();

        $perPage = is_numeric($this->getTableRecordsPerPage()) ? (int) $this->getTableRecordsPerPage() : 10;
        $page = max((int) $this->getTablePage(), 1);
        $items = $records->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $records->count(),
            $perPage,
            $page,
            [
                'path' => \request()->url(),
                'pageName' => 'page',
            ],
        );
    }

    private function getHumanReadableSchedule(string $schedule): ?string
    {
        $humanReadableSchedule = CronExpressionParser::toHumanReadable($schedule);

        if ($humanReadableSchedule === $schedule) {
            return null;
        }

        return ucfirst($humanReadableSchedule);
    }

    private function getScheduleLabel(string $schedule): string
    {
        $humanReadableSchedule = $this->getHumanReadableSchedule($schedule);

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

    private function getScheduleDescription(string $schedule): ?string
    {
        return CronExpressionParser::isValidCronExpression($schedule) ? $schedule : null;
    }

    private function canEditSchedule(string $schedule): bool
    {
        return CronExpressionParser::isValidCronExpression($schedule);
    }
}