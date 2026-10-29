<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin\Controllers;

use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Config\Modules\Admin\Module;
use Hirtz\Config\Modules\Admin\Widgets\Forms\Fields\SensitiveField;
use Hirtz\Skeleton\Web\Controller;
use Override;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @extends Controller<Module>
 */
class ConfigController extends Controller
{
    public $defaultAction = 'update';

    #[Override]
    public function behaviors(): array
    {
        return [
            ...parent::behaviors(),
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['sensitive', 'update'],
                        'roles' => [Config::AUTH_CONFIG],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'sensitive' => ['post'],
                ],
            ],
        ];
    }

    public function actionUpdate(): Response|string
    {
        $config = Config::create();

        if ($config->load($this->request->post())) {
            if ($config->save()) {
                $this->success(Yii::t('config', 'CONFIG_UPDATE_SUCCESS'));
            }

            // An unchanged save ends like a changed one rather than redrawing the posted form
            if (!$config->hasErrors()) {
                return $this->refresh();
            }
        }

        return $this->render('update', [
            'config' => $config,
        ]);
    }

    public function actionSensitive(string $attribute, string $state): string
    {
        $config = Config::create();

        if (!in_array($attribute, $config->activeAttributes(), true) || !$config->isSensitiveAttribute($attribute)) {
            throw new NotFoundHttpException();
        }

        $states = [SensitiveField::STATE_SAVED, SensitiveField::STATE_REPLACE, SensitiveField::STATE_REMOVE];

        if (!in_array($state, $states, true)) {
            throw new NotFoundHttpException();
        }

        return (string)SensitiveField::make()
            ->model($config)
            ->property($attribute)
            ->state($state)
            ->autofocus($state === SensitiveField::STATE_REPLACE);
    }
}
