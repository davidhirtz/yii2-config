<?php

namespace davidhirtz\yii2\config\composer;

use davidhirtz\yii2\skeleton\web\Application;
use yii\base\BootstrapInterface;
use Yii;

/**
 * Class Bootstrap
 * @package davidhirtz\yii2\config\bootstrap
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @param Application $app
     */
    public function bootstrap($app)
    {
        Yii::setAlias('@config', dirname(__DIR__));

        $app->extendComponent('i18n', [
            'translations' => [
                'config' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@config/messages',
                ],
            ],
        ]);

        $app->extendModules([
            'admin' => [
                'modules' => [
                    'config' => [
                        'class' => 'davidhirtz\yii2\config\modules\admin\Module',
                    ],
                ],
            ],
        ]);

        $app->setMigrationNamespace('davidhirtz\yii2\config\migrations');
    }
}