<?php
/**
 * Create redirect form.
 * @see davidhirtz\yii2\config\modules\admin\controllers\ConfigController::actionUpdate()
 *
 * @var View $this
 * @var Config $config
 */

use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\web\View;
use davidhirtz\yii2\skeleton\widgets\bootstrap\Panel;
use yii\helpers\Url;

$this->setTitle($config::getModule()->name);
$this->setBreadcrumb($this->title);
?>
    <h1 class="page-header">
        <a href="<?= Url::toRoute(['update']) ?>"><?= $this->title; ?></a>
    </h1>

<?= Html::errorSummary($config); ?>

<?= Panel::widget([
    'title' => $this->title,
    'content' => $config->getActiveForm()::widget([
        'model' => $config,
    ]),
]);
?>