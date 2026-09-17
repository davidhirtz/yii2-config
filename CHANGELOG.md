## 3.0.0 (in development)

- **The settings page carries a *System* breadcrumb.** `ConfigNavItem` is a child of the skeleton's
  `SystemNavItem`, so `Modules\Admin\Widgets\Navs\ConfigHeader` says so in the bar rather than leaving the
  page looking like a top-level one.

- `Modules\Admin\Widgets\Forms\ConfigActiveForm` declares its fields in `getDefaultRows()` instead of assigning
  `$this->rows ??=` in `configure()`, which the skeleton's `Widgets\Forms\ActiveForm` needs to normalize them
  before an `EVENT_CONFIGURE` listener sees them (monorepo issue #120). A subclass overriding `configure()` to
  change the fields has to move to the hook.

- `Modules\Admin\Models\Config::getModule()` looks the module up on every call instead of memoising it in a
  static, and `Config::reset()` is gone with the static — so is the `Bootstrap` call that cleared it. A module
  belongs to the application that built it, and what the memo saved is the two array reads the skeleton's
  `Modules\ModuleTrait::getModule()` has always done uncached
- **One permission per admin-managed model.** `Modules\Admin\Models\Config::AUTH_CONFIG` (`config`) replaces
  `AUTH_CONFIG_UPDATE`, described by `AUTH_CONFIG_DESCRIPTION`. `Migrations\M260914150000AuthItems` grants it to
  every parent and assignee of the old one
- `Modules\Admin\Models\Config` implements the skeleton's `Models\Interfaces\AdminModelInterface`:
  `getTrailModelName()` is `getAdminType()`, which is what `AdminModelTrait` names a model with no primary key by

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