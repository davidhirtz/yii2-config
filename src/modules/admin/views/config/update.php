<?php
declare(strict_types=1);

/**
 * @see ConfigController::actionUpdate()
 *
 * @var View $this
 * @var Config $config
 */

use Hirtz\Config\modules\admin\controllers\ConfigController;
use Hirtz\Config\modules\admin\models\Config;
use Hirtz\Config\modules\admin\widgets\forms\ConfigActiveForm;
use Hirtz\Config\modules\admin\widgets\navs\ConfigSubmenu;
use Hirtz\Skeleton\helpers\Html;
use Hirtz\Skeleton\web\View;
use Hirtz\Skeleton\widgets\bootstrap\Panel;

$this->title($config::getModule()->getName());
$this->setBreadcrumb($this->title);
?>

<?= ConfigSubmenu::widget(); ?>
<?= Html::errorSummary($config); ?>

<?= Panel::widget([
    'title' => Yii::t('config', 'Update Settings'),
    'content' => ConfigActiveForm::widget([
        'model' => $config,
    ]),
]);
?>
