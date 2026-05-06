<?php

namespace ZPMPackages\FilamentCronManager\Resources\CronEntries;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use ZPMPackages\FilamentCronManager\Resources\CronEntries\Pages\CreateCronEntry;
use ZPMPackages\FilamentCronManager\Resources\CronEntries\Pages\EditCronEntry;
use ZPMPackages\FilamentCronManager\Resources\CronEntries\Pages\ListCronEntries;
use ZPMPackages\FilamentCronManager\Resources\CronEntries\Schemas\CronEntryForm;
use ZPMPackages\FilamentCronManager\Resources\CronEntries\Tables\CronEntriesTable;
use ZPMPackages\LaravelCronManager\Models\CronEntry;

class CronEntryResource extends Resource
{
    protected static ?string $model = CronEntry::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string | \UnitEnum | null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Cron Manager';

    public static function form(Schema $schema): Schema
    {
        return CronEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CronEntriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCronEntries::route('/'),
            'create' => CreateCronEntry::route('/create'),
            'edit' => EditCronEntry::route('/{record}/edit'),
        ];
    }
}