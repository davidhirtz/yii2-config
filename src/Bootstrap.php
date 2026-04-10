<?php

declare(strict_types=1);

namespace Hirtz\Config;

use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Config\Modules\Admin\Module;
use Hirtz\Skeleton\Modules\Admin\Controllers\DashboardController;
use Hirtz\Skeleton\Web\Application;
use Yii;
use yii\base\BootstrapInterface;
use yii\i18n\PhpMessageSource;

class Bootstrap implements BootstrapInterface
{
    /**
     * @param Application $app
     */
    public function bootstrap($app): void
    {
        Yii::setAlias('@config', __DIR__);

        $app->getI18n()->translations['config'] ??= [
            'class' => PhpMessageSource::class,
            'basePath' => '@config/../messages',
        ];

        $app->extendModules([
            'admin' => [
                'modules' => [
                    'config' => [
                        'class' => Module::class
                    ],
                ],
            ],
        ]);

        DashboardController::addRoles([
            Config::AUTH_CONFIG_UPDATE,
        ]);

        $app->setMigrationNamespace('Hirtz\Config\Migrations');
    }
}
