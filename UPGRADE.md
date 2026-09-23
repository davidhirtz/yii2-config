# Upgrading to 3.0

## Requirements

- PHP `^8.3`
- `davidhirtz/yii2-skeleton` `^3.0`; follow the skeleton's `UPGRADE.md` first, this bundle only adds to it
- `config/params.php` (or whatever `configFile` names) must be writable by the web server. v2 set it to
  `0777` through a Composer `postInstall` hook; v3 does not touch the permissions

## Renames

### Namespaces and directories

| v2                                                  | v3                                              |
|-----------------------------------------------------|-------------------------------------------------|
| `davidhirtz\yii2\config\`                           | `Hirtz\Config\`                                 |
| `davidhirtz\yii2\config\modules\admin\`             | `Hirtz\Config\Modules\Admin\`                   |
| `davidhirtz\yii2\config\migrations\`                | `Hirtz\Config\Migrations\`                      |
| `src/messages/`                                     | `messages/`                                     |
| `src/modules/admin/views/config/update.php`         | `resources/views/admin/config/update.php`       |

### Classes

| v2                                                                  | v3                                                      |
|---------------------------------------------------------------------|---------------------------------------------------------|
| `davidhirtz\yii2\config\Bootstrap`                                  | `Bootstrap`                                             |
| `davidhirtz\yii2\config\modules\admin\Module`                       | `Modules\Admin\Module`                                  |
| `davidhirtz\yii2\config\modules\admin\models\Config`                | `Modules\Admin\Models\Config`                           |
| `davidhirtz\yii2\config\modules\admin\controllers\ConfigController` | `Modules\Admin\Controllers\ConfigController`            |
| `davidhirtz\yii2\config\modules\admin\widgets\forms\ConfigActiveForm` | `Modules\Admin\Widgets\Forms\ConfigActiveForm`        |
| `davidhirtz\yii2\config\modules\admin\widgets\navs\ConfigSubmenu`   | removed; `Modules\Admin\Widgets\Navs\ConfigHeader`      |
| —                                                                   | `Modules\Admin\Widgets\Navs\ConfigNavItem` (new)        |
| `davidhirtz\yii2\config\migrations\M220207124544Config`             | `Migrations\M260101000800ConfigBaseline` (fresh installs only) |

### Methods and properties

| v2                                          | v3                                                          |
|---------------------------------------------|-------------------------------------------------------------|
| `Config::getTrailModelName()`               | `Config::getAdminType()`                                    |
| `Config::getUpdatedAt(): bool\|int\|null`   | `Config::getUpdatedAt(): false\|int`                        |
| —                                           | `Config::getPermissionName()` (new, answers `AUTH_CONFIG`)  |
| `Module::$route`                            | removed; `ConfigNavItem` carries the URL                    |
| `Module::getName()`                         | removed; `Yii::t('config', 'CONFIG_NAME')`                  |
| `Module::getNavBarItems()`                  | `Module::aside(Nav $nav)`                                   |
| `Module::getDashboardPanels()`              | `Module::dashboard(Dashboard $dashboard)`                   |
| `Module::getCoreControllerMap()`            | removed; the controller is found by namespace               |
| `ConfigActiveForm::$hasStickyButtons`       | removed                                                     |
| `ConfigActiveForm::init()` assigning `$fields` | `ConfigActiveForm::getDefaultRows()`                     |
| `ConfigActiveForm::renderFooter()`, `getTimestampItems()` | `ConfigActiveForm::getUpdatedAtFooterItem()`  |

### Constants and permissions

| v2                                          | v3                                  |
|---------------------------------------------|-------------------------------------|
| `Config::AUTH_CONFIG_UPDATE` (`configUpdate`) | `Config::AUTH_CONFIG` (`config`)  |

### Routes

| v2                        | v3                               |
|---------------------------|----------------------------------|
| `/admin/config/update`    | `/admin/config/config/update`    |

### Message keys (category `config`)

| v2                              | v3                          |
|---------------------------------|-----------------------------|
| `'Settings'`                    | `CONFIG_NAME`               |
| `'Update Settings'`             | `CONFIG_TITLE`              |
| `'The settings were updated.'`  | `CONFIG_SUCCESS_UPDATED`    |
| `'Update website settings'`     | `AUTH_CONFIG_DESCRIPTION`   |
| —                               | `CONFIG_DASHBOARD_UPDATE` (new) |

## Configuration

Only the class name changes. The container definition that maps the bundle's model to the project's
subclass keeps its shape, and `modules.admin.modules.config.configFile` keeps its name and default:

```php
// v2
'container' => ['definitions' => [
    davidhirtz\yii2\config\modules\admin\models\Config::class => app\models\Config::class,
]],

// v3
'container' => ['definitions' => [
    Hirtz\Config\Modules\Admin\Models\Config::class => App\Models\Config::class,
]],
```

A `Module::$route` in the module configuration is an unknown property now. Point the nav item somewhere
else through a `Widget::EVENT_CONFIGURE` listener on `ConfigNavItem` instead.

## Code changes

### The project's `Config` subclass

The base class implements `Hirtz\Skeleton\Models\Interfaces\TrailModelInterface`, so a subclass inherits
`getAdminRoute()`, `getPermissionName()` and `getAdminType()`. Nothing changes for the attributes, `rules()`
and `attributeLabels()`; `i18nAttributes` still works, with the rules wrapped in `getI18nRules()` as before. Two
overrides move:

```php
// v2
public function getTrailModelName(): string { return 'Site settings'; }

// v3
#[\Override]
public function getAdminType(): string { return Yii::t('app', 'Site settings'); }
```

`getUpdatedAt()` answers `false` rather than `null` for a missing file.

### The permission name

`can('configUpdate')` and an `AccessRule` naming it answer `false` silently after the migration below has
deleted the item. Replace every occurrence with `Config::AUTH_CONFIG`:

```php
// v2
'roles' => [Config::AUTH_CONFIG_UPDATE],

// v3
'roles' => [Config::AUTH_CONFIG],
```

### A `ConfigActiveForm` subclass

The form is a skeleton `Widgets\Forms\ActiveForm` now. Fields are declared in `getDefaultRows()`, the
footer item in `getUpdatedAtFooterItem()`; `init()`, `$fields`, `$hasStickyButtons`, `renderFooter()` and
`getTimestampItems()` are gone:

```php
// v2
public function init(): void
{
    $this->fields = [['title'], ['email']];
    parent::init();
}

// v3
#[\Override]
protected function getDefaultRows(): array
{
    return [['title'], ['email']];
}
```

### The module's nav item and dashboard

`getNavBarItems()`, `getDashboardPanels()` and `getName()` are gone with the skeleton's array-based navigation.
A project that changed the nav item's label, icon or URL through a `Module` subclass listens for
`Widget::EVENT_CONFIGURE` on `ConfigNavItem` instead, or overrides `Module::aside()` and `Module::dashboard()`.

### An overridden view

A project view overriding `update.php` renders `ConfigHeader::make()` and a `FormContainer` around
`ConfigActiveForm::make()->model($config)` in place of `ConfigSubmenu::widget()`, `Html::errorSummary()` and
the bootstrap `Panel`; the shipped view is the template.

### Message keys

`Yii::t('config', 'Settings')` and the other three English texts are keys now (table above). A project
translating this category in its own `messages/` mirrors the new keys.

## Data and schema

The bundle has no tables of its own; the upgrade touches the RBAC tables only. The v2 → v3 migration lives
in `davidhirtz/yii2-upgrade` under `migrations/yii2-config/`, and the upgrade tool's collapse script squashes
both it and the v2 `M220207124544Config` onto the baseline in the migration history:

1. `M260914150000AuthItems` creates the `config` permission with its `AUTH_CONFIG_DESCRIPTION` pointer under
   `admin`, grants it to every role that held `configUpdate` and to every user `configUpdate` was assigned
   to, then deletes `configUpdate`. The skeleton's `M260914190000ManagerRole` runs after it and grants
   `config` to `manager` as well.

Before: a backup of the database. After: nothing bundle-specific. The trail rows an edited settings file
wrote keep their history; the upgrade tool's collapse script re-points `trail.model` to the renamed
project class along with every other model.

Lost: the hierarchy of a project permission that had `configUpdate` as a child; the replacement is granted
flat to the same parents.

## Removed

- `Modules\Admin\Widgets\Navs\ConfigSubmenu`; the page has a header with a *System* breadcrumb instead
- `Module::$route`, `Module::getName()`, `Module::getNavBarItems()`, `Module::getDashboardPanels()`,
  `Module::getCoreControllerMap()`
- `ConfigActiveForm::$hasStickyButtons`, `renderFooter()`, `getTimestampItems()`
- The `zh-CN` and `zh-TW` message files
- The Composer `postInstall` hook setting `config/params.php` to `0777`
