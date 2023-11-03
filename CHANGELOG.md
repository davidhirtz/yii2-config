## v2.0.0 (Nov 2, 2023)

- Moved source code to `src` folder
- Moved model and form widgets out of `base` folder, to override them use Yii's dependency injection
    container
- Removed `Config::getActiveForm()`, to override the active form, use Yii's dependency injection
    container