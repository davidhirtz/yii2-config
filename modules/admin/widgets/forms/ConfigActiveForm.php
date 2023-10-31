<?php

namespace davidhirtz\yii2\config\modules\admin\widgets\forms;

use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\widgets\bootstrap\ActiveForm;
use davidhirtz\yii2\timeago\Timeago;
use Yii;

/**
 * Class ConfigActiveForm
 * @package davidhirtz\yii2\config\modules\admin\widgets\forms
 *
 * @property Config $model
 */
class ConfigActiveForm extends ActiveForm
{
    /**
     * @var bool
     */
    public bool $hasStickyButtons = true;

    /**
     * @inheritDoc
     */
    public function init()
    {
        if (!$this->fields) {
            $this->fields = [];

            foreach ($this->model->activeAttributes() as $attribute) {
                $this->fields[] = [$attribute];
            }
        }

        parent::init();
    }

    /**
     * @return void
     */
    public function renderFooter()
    {
        echo $this->listRow($this->getTimestampItems());
    }

    /**
     * @return array
     */
    protected function getTimestampItems()
    {
        $text = Yii::t('skeleton', 'Last updated {timestamp}', [
            'timestamp' => Timeago::tag($this->model->getUpdatedAt()),
        ]);

        return [
            Yii::$app->getUser()->can('trailIndex') ? Html::a($text, ['/admin/trail/index', 'model' => Config::class]) : $text,
        ];
    }
}