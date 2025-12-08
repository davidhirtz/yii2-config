<?php

declare(strict_types=1);

namespace Hirtz\Config\modules\admin\widgets\forms;

use Hirtz\Config\modules\admin\models\Config;
use Hirtz\Skeleton\helpers\Html;
use Hirtz\Skeleton\models\Trail;
use Hirtz\Skeleton\widgets\bootstrap\ActiveForm;
use Hirtz\Timeago\Timeago;
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
