<?php

declare(strict_types=1);

namespace Hirtz\Config;

use Hirtz\Config\modules\admin\Module;
use Hirtz\Skeleton\web\Application;
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
            'basePath' => '@config/messages',
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

        $app->setMigrationNamespace('Hirtz\Config\migrations');
    }
}
