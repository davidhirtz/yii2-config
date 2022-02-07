<?php

namespace davidhirtz\yii2\config\modules\admin;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class Module
 * @package davidhirtz\yii2\config\modules\admin
 * @property \davidhirtz\yii2\skeleton\modules\admin\Module $module
 */
class Module extends \yii\base\Module
{
    /**
     * @var string the module display name, defaults to "Settings"
     */
    public $name;

    /**
     * @var string
     */
    public $configFile = '@app/config/params.php';

    /**
     * @var mixed the navbar item url
     */
    public $url = ['/admin/config/update'];

    /**
     * @var string
     */
    public $defaultRoute = 'config';

    /**
     * @var array containing the admin menu items
     */
    public $navbarItems = [];

    /**
     * @var array containing the panel items
     */
    public $panels = [];

    /**
     * @var array
     */
    protected $defaultControllerMap = [
        'config' => [
            'class' => 'davidhirtz\yii2\config\modules\admin\controllers\ConfigController',
            'viewPath' => '@config/modules/admin/views/config',
        ],
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->name) {
            $this->name = Yii::t('config', 'Settings');
        }

        if (!Yii::$app->getRequest()->getIsConsoleRequest()) {
            if (!$this->navbarItems) {
                $this->navbarItems = [
                    'cms' => [
                        'label' => $this->name,
                        'icon' => 'cogs',
                        'url' => $this->url,
                        'active' => ['admin/config/'],
                        'roles' => ['configUpdate', 'configView'],
                    ]
                ];
            }

            if ($this->panels) {
                $this->module->panels = array_merge($this->module->panels, $this->panels);
            } else {
                $this->module->panels['skeleton'][] = [
                    'label' => Yii::t('config', 'Settings'),
                    'url' => ['/admin/config/index'],
                    'icon' => 'cogs',
                    'roles' => ['configUpdate', 'configView'],
                ];
            }

            $this->module->navbarItems = array_merge($this->module->navbarItems, $this->navbarItems);
        }

        $this->module->controllerMap = ArrayHelper::merge(array_merge($this->module->controllerMap, $this->defaultControllerMap), $this->controllerMap);
        parent::init();
    }
}