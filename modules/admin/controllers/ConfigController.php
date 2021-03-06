<?php

namespace davidhirtz\yii2\config\modules\admin\controllers;

use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\skeleton\web\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * Class ConfigController
 * @package davidhirtz\yii2\config\modules\admin\controllers
 */
class ConfigController extends Controller
{
    /**
     * @var string
     */
    public $defaultAction = 'update';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'roles' => [Config::AUTH_CONFIG_UPDATE],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return string|Response
     */
    public function actionUpdate()
    {
        $config = new Config();

        if ($config->load(Yii::$app->getRequest()->post()) && $config->save()) {
            $this->success(Yii::t('config', 'The settings were updated.'));
        }

        return $this->render('update', [
            'config' => $config,
        ]);
    }
}