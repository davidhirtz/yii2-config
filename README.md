# yii2-config

Makes a project's application params editable in the admin. A project subclasses
`Hirtz\Config\Modules\Admin\Models\Config`, declares the editable params as attributes with rules and labels,
and the bundle renders them as a form under *System*. Saving writes the values back into the params file
(`config/params.php` by default), records a trail entry and updates `Yii::$app->params` for the running
request. Depends on `davidhirtz/yii2-skeleton` only.

## Installation

```bash
composer require davidhirtz/yii2-config
```

The bundle bootstraps itself through `extra.bootstrap` (`Hirtz\Config\Bootstrap`): it registers the `@config`
alias, the `config` message category, the `config` submodule of the admin module and its migration namespace,
and adds `Config::AUTH_CONFIG` to the roles the admin dashboard offers. Then:

```bash
./yii migrate
```

The migration creates the `config` permission, granted to the `admin` and `manager` roles. The params file
must be writable by the web server; the bundle creates its directory but sets no permissions.

## Configuration

Module properties, under `modules.admin.modules.config`:

| Property       | Default                       | Meaning                                                        |
|----------------|-------------------------------|----------------------------------------------------------------|
| `configFile`   | `'@root/config/params.php'`   | The params file the form reads and writes, as an alias or path |
| `defaultRoute` | `'config'`                    | Yii's default controller, so `/admin/config` opens the form    |

The bundle reads no component and no param of its own. The one definition a project sets is the container
mapping of the model to its own subclass, plus the subclass itself:

```php
// config/web.php
'container' => [
    'definitions' => [
        Hirtz\Config\Modules\Admin\Models\Config::class => App\Models\Config::class,
    ],
],
```

```php
// app/Models/Config.php
namespace App\Models;

class Config extends \Hirtz\Config\Modules\Admin\Models\Config
{
    public array $i18nAttributes = ['claim'];

    #[\Override]
    public function rules(): array
    {
        return $this->getI18nRules([
            [['contactEmail', 'claim'], 'trim'],
            [['contactEmail'], 'email'],
            [['claim'], 'string', 'max' => 250],
        ]);
    }

    #[\Override]
    public function attributeLabels(): array
    {
        return [
            'contactEmail' => Yii::t('app', 'Contact email'),
            'claim' => Yii::t('app', 'Claim'),
        ];
    }
}
```

What the form shows is `activeAttributes()`: a param is editable when a validation rule names it, and every
other key in the file survives a save untouched. `i18nAttributes` clones a field per configured language and
stores `claim_de` beside `claim` in the same file; the rules go through `getI18nRules()`, which is what
declares the `_de` variant, since nothing on a plain `yii\base\Model` does it for the subclass.

## The admin page

- `Modules\Admin\Widgets\Navs\ConfigNavItem` is added below the skeleton's *System* nav item, and
  `Module::dashboard()` adds a *Manage settings* item to the dashboard; both point at
  `/admin/config/config/update`.
- `Modules\Admin\Controllers\ConfigController::actionUpdate()` is guarded by `Config::AUTH_CONFIG` (`config`).
  The permission is described by the message key `AUTH_CONFIG_DESCRIPTION`.
- `Modules\Admin\Widgets\Forms\ConfigActiveForm` renders one row per active attribute, in the order
  `activeAttributes()` answers, with the file's modification time in the footer; a project changes the rows
  through a `Widget::EVENT_CONFIGURE` listener or a subclass overriding `getDefaultRows()`, mapped in the
  container.
- `Modules\Admin\Widgets\Navs\ConfigHeader` titles the page with `CONFIG_TITLE` and adds the *System*
  breadcrumb.
- Every save is a trail entry: `Config` implements `Hirtz\Skeleton\Models\Interfaces\TrailModelInterface`,
  named in the trail by `getAdminType()` (`CONFIG_NAME`).

## Console commands

None.
