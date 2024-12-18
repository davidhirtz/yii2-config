<?php

declare(strict_types=1);

namespace davidhirtz\yii2\config\modules\admin;

use davidhirtz\yii2\config\modules\admin\controllers\ConfigController;
use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\skeleton\modules\admin\ModuleInterface;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property \davidhirtz\yii2\skeleton\modules\admin\Module $module
 */
class Module extends \davidhirtz\yii2\skeleton\base\Module implements ModuleInterface
{
    /**
     * @var string|null the module display name, defaults to "Settings"
     */
    public ?string $name = null;

    /**
     * @var string the config file path
     */
    public string $configFile = '@root/config/params.php';

    /**
     * @var array|string the navbar item url
     */
    public array|string $url = ['/admin/config/update'];

    public function init(): void
    {
        $this->name ??= Yii::t('config', 'Settings');
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

    public function getNavBarItems(): array
    {
        return [
            'config' => [
                'label' => $this->name,
                'icon' => 'cogs',
                'url' => $this->url,
                'active' => ['admin/config/'],
                'roles' => [Config::AUTH_CONFIG_UPDATE],
                'order' => 100,
            ],
        ];
    }
}
