## 2.2.1 (Dev 18, 2024)

- Fixed module name initialization

## 2.2.0 (Jul 31, 2024)

- Added I18N support for config
- Enhanced `Config` model to not complain about unused properties that are not set as long as they are set
  in `activeAttributes()`

## 2.1.7 (Apr 5, 2024)

- Updated admin according to `davidhirtz\yii2\skeleton\modules\admin\ModuleInterface`

## 2.1.6 (Mar 21, 2024)

- Updated `Module::$navbarItems` default `order` value to `100`

## 2.1.5 (Feb 29, 2024)

- Updated dependencies

## 2.1.4 (Feb 27, 2024)

- Added `ModelTrait` to `Config` class, to allow the use of `Config::create()`

## 2.1.3 (Feb 27, 2024)

- Fixed namespace of `davidhirtz\yii2\config\modules\admin\widgets\navs\ConfigSubmenu`

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