<?php

namespace davidhirtz\yii2\config\modules\admin\widgets\forms;

use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\skeleton\helpers\Html;
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
        if (!$this->fields) {
            $this->fields = [];

            foreach ($this->model->activeAttributes() as $attribute) {
                $this->fields[] = [$attribute];
            }
        }

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
            Yii::$app->getUser()->can('trailIndex') ? Html::a($text, ['/admin/trail/index', 'model' => $this->model::class]) : $text,
        ];
    }
}