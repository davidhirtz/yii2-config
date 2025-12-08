<?php

declare(strict_types=1);

namespace Hirtz\Config\modules\admin;

use Hirtz\Config\Modules\Admin\Controllers\ConfigController;
use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Skeleton\Modules\Admin\Config\MainMenuItemConfig;
use Hirtz\Skeleton\Modules\Admin\ModuleInterface;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property \Hirtz\Skeleton\Modules\Admin\Module $module
 */
class Module extends \Hirtz\Skeleton\Base\Module implements ModuleInterface
{
    public string $configFile = '@root/config/params.php';
    public array|string $url = ['/admin/config/update'];

    public function init(): void
    {
        $this->controllerMap = ArrayHelper::merge($this->getCoreControllerMap(), $this->controllerMap);
        parent::init();
    }

    protected function getCoreControllerMap(): array
    {
        return [
            'config' => [
                'class' => ConfigController::class,
                'viewPath' => '@config/modules/admin/views/config',
            ],
        ];
    }

    public function getDashboardPanels(): array
    {
        return [];
    }

    public function getName(): string
    {
        return Yii::t('config', 'Settings');
    }

    public function getMainMenuItems(): array
    {
        return [
            'config' => new MainMenuItemConfig(
                label: $this->getName(),
                url: $this->url,
                icon: 'cogs',
                routes: ['admin/config/'],
                roles: [Config::AUTH_CONFIG_UPDATE],
                order: 100,
            ),
        ];
    }
}
