<?php

declare(strict_types=1);

namespace davidhirtz\yii2\config\modules\admin\widgets\forms;

use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\models\Trail;
use davidhirtz\yii2\skeleton\widgets\bootstrap\ActiveForm;
use davidhirtz\yii2\timeago\Timeago;
use Yii;

/**
 * @property Config $model
 */
class ConfigActiveForm extends ActiveForm
{
    public bool $hasStickyButtons = true;

    public function init(): void
    {
        $this->fields ??= array_map(fn ($attribute) => [$attribute], $this->model->activeAttributes());
        $this->i18nAttributes = [];

        parent::init();
    }

    public function renderFooter(): void
    {
        echo $this->listRow($this->getTimestampItems());
    }

    protected function getTimestampItems(): array
    {
        $text = Yii::t('skeleton', 'Last updated {timestamp}', [
            'timestamp' => Timeago::tag($this->model->getUpdatedAt()),
        ]);

        return [
            Yii::$app->getUser()->can('trailIndex') ? Html::a($text, Trail::getAdminRouteByModel($this->model)) : $text,
        ];
    }
}
