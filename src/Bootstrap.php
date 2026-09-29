<?php

declare(strict_types=1);

namespace Hirtz\Config;

use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Config\Modules\Admin\Module;
use Hirtz\Skeleton\Base\ConfigBootstrapInterface;
use Hirtz\Skeleton\Modules\Admin\Controllers\DashboardController;
use Hirtz\Skeleton\Web\Application;
use Override;
use Yii;
use yii\i18n\PhpMessageSource;

class Bootstrap implements ConfigBootstrapInterface
{
    #[Override]
    public static function getDefaultConfig(): array
    {
        return [
            'components' => [
                'i18n' => [
                    'translations' => [
                        'config' => [
                            'class' => PhpMessageSource::class,
                            'basePath' => '@config/../messages',
                            'forceTranslation' => true,
                        ],
                    ],
                ],
            ],
            'modules' => [
                'admin' => [
                    'modules' => [
                        'config' => [
                            'class' => Module::class,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param Application<\Hirtz\Skeleton\Models\User> $app
     */
    public function bootstrap($app): void
    {
        Yii::setAlias('@config', __DIR__);

        DashboardController::addRoles(static fn (): array => [
            Config::AUTH_CONFIG,
        ]);

        $app->setMigrationNamespace('Hirtz\Config\Migrations');
    }
}
