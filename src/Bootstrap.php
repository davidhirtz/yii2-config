<?php

namespace davidhirtz\yii2\config;

use davidhirtz\yii2\config\modules\admin\Module;
use davidhirtz\yii2\skeleton\web\Application;
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

        $app->extendComponent('i18n', [
            'translations' => [
                'config' => [
                    'class' => PhpMessageSource::class,
                    'basePath' => '@config/messages',
                ],
            ],
        ]);

        $app->extendModules([
            'admin' => [
                'modules' => [
                    'config' => [
                        'class' => Module::class
                    ],
                ],
            ],
        ]);

        $app->setMigrationNamespace('davidhirtz\yii2\config\migrations');
    }
}
