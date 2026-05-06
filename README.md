# Filament Cron Manager

Filament plugin for `zpm-packages/cron-manager-laravel` that exposes cron management through the admin panel.

## Installation

```bash
composer require zpm-packages/cron-manager-filament
```

Register the plugin on your panel:

```php
use ZPMPackages\FilamentCronManager\FilamentCronManagerPlugin;

$panel->plugin(FilamentCronManagerPlugin::make());
```

## Behavior

- when `cron-manager.sync_with_database` is `false`, the plugin registers a direct system management page
- when `cron-manager.sync_with_database` is `true`, the plugin registers a resource backed by the database table

On Windows, the direct system management page can show both package-managed cron jobs and imported Task Scheduler entries. Imported tasks are mapped best-effort from their triggers and may use descriptive schedule labels instead of a raw cron expression.

Visibility is configurable in `config/cron-manager.php`:

- `cron-manager.system_page.show_managed_cron_jobs`
- `cron-manager.system_page.show_system_task_schedules`

## Schedule UX

In direct system mode, the create and edit forms use a preset schedule select.

In database-synced mode, the resource form still suggests common schedules like `every minute`, `every 5 minutes`, `every hour`, and `every day` while allowing custom cron expressions.

The command field also includes a tip for a quick smoke test:

```bash
php /schedule.php "Cron comment"
```

Expected output:

```text
Cron comment ran at 2026-05-06 13:45:00
```

Monthly and yearly presets are surfaced as Unix-only options because the Windows Task Scheduler adapter supports only the common minute, hour, day, and week-based schedules for creation.

The schedule column in both Filament tables now prefers a human-readable primary label, shows the raw cron expression underneath when available, and wraps long schedule text within a 400px column.