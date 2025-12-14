<?php
declare(strict_types=1);

/**
 * @see ConfigController::actionUpdate()
 *
 * @var View $this
 * @var Config $config
 */

use Hirtz\Config\Modules\Admin\Controllers\ConfigController;
use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Config\Modules\Admin\Widgets\Forms\ConfigActiveForm;
use Hirtz\Config\Modules\Admin\Widgets\Navs\ConfigSubmenu;
use Hirtz\Skeleton\Helpers\Html;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Bootstrap\Panel;

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
