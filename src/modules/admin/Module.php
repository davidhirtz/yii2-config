<?php

declare(strict_types=1);

namespace davidhirtz\yii2\config\modules\admin;

use davidhirtz\yii2\config\modules\admin\controllers\ConfigController;
use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\skeleton\modules\admin\ModuleInterface;
use Override;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property \davidhirtz\yii2\skeleton\modules\admin\Module $module
 */
class Module extends \davidhirtz\yii2\skeleton\base\Module implements ModuleInterface
{
    /**
     * @var string the config file path
     */
    public string $configFile = '@root/config/params.php';

    /**
     * @var array the navbar item url
     */
    public array $route = ['/admin/config/update'];

    #[Override]
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

    public function getNavBarItems(): array
    {
        return [
            'config' => [
                'label' => $this->getName(),
                'icon' => 'cogs',
                'url' => $this->route,
                'active' => ['admin/config/'],
                'roles' => [Config::AUTH_CONFIG_UPDATE],
                'order' => 100,
            ],
        ];
    }
}
