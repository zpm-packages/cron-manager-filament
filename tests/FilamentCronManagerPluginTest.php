<?php

namespace ZPMPackages\FilamentCronManager\Tests;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Panel;
use Filament\Support\ArrayRecord;
use Filament\Tables\Table;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use ZPMPackages\FilamentCronManager\FilamentCronManagerPlugin;
use ZPMPackages\FilamentCronManager\Pages\ManageSystemCronsPage;
use ZPMPackages\FilamentCronManager\Resources\CronEntries\Tables\CronEntriesTable;
use ZPMPackages\FilamentCronManager\Resources\CronEntries\CronEntryResource;
use ZPMPackages\LaravelCronManager\Contracts\CronCrudService;
use ZPMPackages\LaravelCronManager\Data\CronEntryData;
use ZPMPackages\LaravelCronManager\Models\CronEntry;

class FilamentCronManagerPluginTest extends TestCase
{
    public function testPluginRegistersSystemManagementPageWhenDatabaseSyncIsDisabled(): void
    {
        config()->set('cron-manager.sync_with_database', false);

        $panel = Panel::make()->id('admin');
        FilamentCronManagerPlugin::make()->register($panel);

        $this->assertContains(ManageSystemCronsPage::class, $panel->getPages());
        $this->assertNotContains(CronEntryResource::class, $panel->getResources());
    }

    public function testPluginRegistersResourceWhenDatabaseSyncIsEnabled(): void
    {
        config()->set('cron-manager.sync_with_database', true);

        $panel = Panel::make()->id('admin');
        FilamentCronManagerPlugin::make()->register($panel);

        $this->assertContains(CronEntryResource::class, $panel->getResources());
        $this->assertNotContains(ManageSystemCronsPage::class, $panel->getPages());
    }

    public function testSystemManagementPageUsesGenericRecordActionsForArrayRecords(): void
    {
        $page = app(ManageSystemCronsPage::class);
        $table = $page->table(Table::make($page));
        $actions = $table->getFlatRecordActions();

        $this->assertArrayHasKey('edit', $actions);
        $this->assertInstanceOf(Action::class, $actions['edit']);
        $this->assertSame(Action::class, $actions['edit']::class);
        $this->assertArrayHasKey('delete', $actions);
        $this->assertInstanceOf(Action::class, $actions['delete']);
        $this->assertSame(Action::class, $actions['delete']::class);
    }

    public function testSystemManagementPageBuildsArrayRecordsWithStableInternalKeys(): void
    {
        app()->instance(CronCrudService::class, new class implements CronCrudService
        {
            public function list(): Collection
            {
                return collect([
                    new CronEntryData(
                        identifier: 'system:0',
                        schedule: '* * * * *',
                        command: 'php schedule.php',
                        comment: 'Test task',
                        databaseId: null,
                        systemIndex: 0,
                    ),
                ]);
            }

            public function create(array $attributes): CronEntryData
            {
                throw new \BadMethodCallException('Not implemented for this test.');
            }

            public function update(int|string $identifier, array $attributes): CronEntryData
            {
                throw new \BadMethodCallException('Not implemented for this test.');
            }

            public function delete(int|string $identifier): bool
            {
                throw new \BadMethodCallException('Not implemented for this test.');
            }
        });

        app()->instance('request', Request::create('/admin/manage-system-crons-page', 'GET'));

        $page = app(ManageSystemCronsPage::class);
        $page->mountInteractsWithTable();
        $page->bootedInteractsWithTable();
        $records = $page->getTableRecords();
        $firstRecord = array_values($records->items())[0];

        $this->assertInstanceOf(LengthAwarePaginator::class, $records);
        $this->assertSame('system:0', $firstRecord[ArrayRecord::getKeyName()]);
        $this->assertSame('system:0', $firstRecord['identifier']);
    }

    public function testTablesShowHumanReadableScheduleLabelsAndRawCronDescriptions(): void
    {
        $page = app(ManageSystemCronsPage::class);

        $systemTable = $page->table(Table::make($page));
        $systemScheduleColumn = $systemTable->getColumn('schedule');
        $systemCronRecord = [
            'identifier' => 'system:hourly',
            'schedule' => '0 * * * *',
        ];
        $systemEventRecord = [
            'identifier' => 'system:event',
            'schedule' => 'Event trigger',
        ];

        $this->assertSame(
            'Every hour',
            $systemScheduleColumn->record($systemCronRecord)->getState(),
        );

        $this->assertSame(
            '0 * * * *',
            $systemScheduleColumn->record($systemCronRecord)->getDescriptionBelow(),
        );

        $this->assertSame(
            'Runs on a system event',
            $systemScheduleColumn->record($systemEventRecord)->getState(),
        );

        $this->assertSame(
            ['style' => 'max-width: 400px; white-space: normal;'],
            $systemScheduleColumn->getExtraAttributes(),
        );

        $resourceTable = CronEntriesTable::configure(Table::make($page));
        $resourceScheduleColumn = $resourceTable->getColumn('schedule');

        $this->assertSame(
            'Every hour',
            $resourceScheduleColumn->record(new CronEntry(['schedule' => '0 * * * *']))->getState(),
        );

        $this->assertSame(
            '0 * * * *',
            $resourceScheduleColumn->record(new CronEntry(['schedule' => '0 * * * *']))->getDescriptionBelow(),
        );

        $this->assertSame(
            ['style' => 'max-width: 400px; white-space: normal;'],
            $resourceScheduleColumn->getExtraAttributes(),
        );
    }

    public function testSystemManagementPageUsesSelectFieldForSchedule(): void
    {
        $page = app(ManageSystemCronsPage::class);
        $reflection = new \ReflectionClass($page);
        $method = $reflection->getMethod('formSchema');
        $schema = $method->invoke($page);

        $this->assertInstanceOf(Select::class, $schema[0]);
        $this->assertSame('schedule', $schema[0]->getName());
    }

    public function testSystemManagementPageHidesEditActionForNonCronSchedules(): void
    {
        $page = app(ManageSystemCronsPage::class);
        $table = $page->table(Table::make($page));
        $actions = $table->getFlatRecordActions();

        $this->assertFalse($actions['edit']->record(['schedule' => '31 * * * *'])->isHidden());
        $this->assertTrue($actions['edit']->record(['schedule' => 'At log on of any user'])->isHidden());
    }
}