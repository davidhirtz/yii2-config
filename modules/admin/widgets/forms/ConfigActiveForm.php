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

    public function renderFooter()
    {
        $text = Yii::t('skeleton', 'Last updated {timestamp}', [
            'timestamp' => Timeago::tag($this->model->getUpdatedAt()),
        ]);

        echo $this->listRow([
            Yii::$app->getUser()->can('trailIndex') ? Html::a($text, ['/admin/trail/index', 'model' => Config::class]) : $text,
        ]);
    }
}