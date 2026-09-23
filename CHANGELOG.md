## 3.0.0 (in development)

- Renamed the namespace from `davidhirtz\yii2\config\` to `Hirtz\Config\` and every directory to StudlyCase; the message files moved from `src/messages/` to `messages/`, the views from `src/modules/admin/views/config/` to `resources/views/admin/config/`
- Replaced the permission `configUpdate` (`Config::AUTH_CONFIG_UPDATE`) with `config` (`Config::AUTH_CONFIG`), described by the message key `AUTH_CONFIG_DESCRIPTION` and granted to the `admin` and `manager` roles
- Replaced the English message texts with keys: `CONFIG_NAME`, `CONFIG_TITLE`, `CONFIG_SUCCESS_UPDATED`, `CONFIG_DASHBOARD_UPDATE` and `AUTH_CONFIG_DESCRIPTION`; removed the `zh-CN` and `zh-TW` message files
- Removed `Module::$route`, `Module::getName()`, `Module::getNavBarItems()` and `Module::getDashboardPanels()`; the module implements `aside()` and `dashboard()` of `Hirtz\Skeleton\Modules\Admin\ModuleInterface`, adding `Modules\Admin\Widgets\Navs\ConfigNavItem` below the skeleton's *System* nav item and a dashboard item, both pointing at `/admin/config/config/update`
- Removed `Modules\Admin\Widgets\Navs\ConfigSubmenu`; the update view renders `Modules\Admin\Widgets\Navs\ConfigHeader`, which carries a *System* breadcrumb
- Changed `Modules\Admin\Models\Config` to implement `Hirtz\Skeleton\Models\Interfaces\TrailModelInterface`: `getTrailModelName()` is now `getAdminType()`, `getPermissionName()` answers `AUTH_CONFIG`, and `getUpdatedAt()` returns `false|int` instead of `bool|int|null`
- Changed `Modules\Admin\Widgets\Forms\ConfigActiveForm` to extend `Hirtz\Skeleton\Widgets\Forms\ActiveForm`: the fields are declared in `getDefaultRows()` and the footer in `getUpdatedAtFooterItem()`; removed `$hasStickyButtons`, `renderFooter()` and `getTimestampItems()`
- Replaced `Migrations\M220207124544Config` with the fresh-install baseline `Migrations\M260101000800ConfigBaseline`; an existing database is upgraded through `davidhirtz/yii2-upgrade`
- Removed the Composer `postInstall` hook that set `config/params.php` to `0777`; the file has to be writable by the web server

## 2.2.2 (Jan 23, 2025)

- Changed `Bootstrap` I18N configuration

## 2.2.1 (Dev 18, 2024)

- Fixed module name initialization
- Fixed initial trail record

## 2.2.0 (Jul 31, 2024)

- Added I18N support for config
- Enhanced `Config` model to not complain about unused properties that are not set as long as they are set
  in `activeAttributes()`

## 2.1.7 (Apr 5, 2024)

- Updated admin according to `Hirtz\Skeleton\Modules\Admin\ModuleInterface`

## 2.1.6 (Mar 21, 2024)

- Updated `Module::$navbarItems` default `order` value to `100`

## 2.1.5 (Feb 29, 2024)

- Updated dependencies

## 2.1.4 (Feb 27, 2024)

- Added `ModelTrait` to `Config` class, to allow the use of `Config::create()`

## 2.1.3 (Feb 27, 2024)

- Fixed namespace of `Hirtz\Config\Modules\Admin\Widgets\Navs\ConfigSubmenu`

## 2.1.2 (Jan 29, 2024)

- Updated dependencies

## 2.1.1 (Dec 21, 2023)

- Fixed corrupted git merge

## 2.1.0 (Dec 21, 2023)

- Added Codeception test suite
- Added GitHub Actions CI workflow

## v2.0.2 (Nov 6, 2023)

- Using the new `@config` alias for the `configFile` pathname

## v2.0.1 (Nov 6, 2023)

- Moved `Bootstrap` class to base package namespace for consistency

## v2.0.0 (Nov 2, 2023)

- Moved source code to `src` folder
- Moved model and form widgets out of `base` folder, to override them use Yii's dependency injection
  container
- Removed `Config::getActiveForm()`, to override the active form, use Yii's dependency injection
  container