<?php
/**
 * Configuration form.
 * @see davidhirtz\yii2\config\modules\admin\controllers\ConfigController::actionUpdate()
 *
 * @var View $this
 * @var Config $config
 */

use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\config\modules\admin\widgets\nav\ConfigSubmenu;
use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\web\View;
use davidhirtz\yii2\skeleton\widgets\bootstrap\Panel;

$this->setTitle($config::getModule()->name);
$this->setBreadcrumb($this->title);
?>

<?= ConfigSubmenu::widget(); ?>
<?= Html::errorSummary($config); ?>

<?= Panel::widget([
    'title' => Yii::t('config', 'Update Settings'),
    'content' => $config->getActiveForm()::widget([
        'model' => $config,
    ]),
]);
?>