<?php

namespace davidhirtz\yii2\config\modules\admin;

use davidhirtz\yii2\config\modules\admin\controllers\ConfigController;
use davidhirtz\yii2\config\modules\admin\models\Config;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property \davidhirtz\yii2\skeleton\modules\admin\Module $module
 */
class Module extends \yii\base\Module
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
     * @var mixed the navbar item url
     */
    public array|string $url = ['/admin/config/update'];

    /**
     * @var array containing the admin menu items
     */
    public array $navbarItems = [];

    /**
     * @var array containing the panel items
     */
    public array $panels = [];

    /**
     * @var array
     */
    protected array $defaultControllerMap = [
        'config' => [
            'class' => ConfigController::class,
            'viewPath' => '@config/modules/admin/views/config',
        ],
    ];

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->name ??= Yii::t('config', 'Settings');

        if (!Yii::$app->getRequest()->getIsConsoleRequest()) {
            if (!$this->navbarItems) {
                // Settings should come in last, so the config param "zz-config" is used
                $this->navbarItems = [
                    'zz-config' => [
                        'label' => $this->name,
                        'icon' => 'cogs',
                        'url' => $this->url,
                        'active' => ['admin/config/'],
                        'roles' => [Config::AUTH_CONFIG_UPDATE],
                    ],
                ];
            }

            if ($this->panels) {
                $this->module->panels = array_merge($this->module->panels, $this->panels);
            } else {
                $this->module->panels['skeleton'][] = [
                    'label' => Yii::t('config', 'Settings'),
                    'url' => ['/admin/config/index'],
                    'icon' => 'cogs',
                    'roles' => [Config::AUTH_CONFIG_UPDATE],
                ];
            }

            $this->module->navbarItems = array_merge($this->module->navbarItems, $this->navbarItems);
        }

        $this->module->controllerMap = ArrayHelper::merge(array_merge($this->module->controllerMap, $this->defaultControllerMap), $this->controllerMap);
        parent::init();
    }
}