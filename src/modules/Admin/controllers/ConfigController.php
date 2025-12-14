<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin\Controllers;

use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Skeleton\Web\Controller;
use Yii;
use yii\filters\AccessControl;
use yii\web\Response;

class ConfigController extends Controller
{
    public $defaultAction = 'update';

    #[\Override]
    public function behaviors(): array
    {
        return [...parent::behaviors(), 'access' => [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['update'],
                    'roles' => [Config::AUTH_CONFIG_UPDATE],
                ],
            ],
        ]];
    }

    public function actionUpdate(): Response|string
    {
        $config = Config::create();

        if ($config->load(Yii::$app->getRequest()->post()) && $config->save()) {
            $this->success(Yii::t('config', 'The settings were updated.'));
            return $this->refresh();
        }

        return $this->render('update', [
            'config' => $config,
        ]);
    }
}
