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
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Forms\FormContainer;

$this->title($config::getModule()->getName());
$this->addBreadcrumb($this->title);

echo ConfigSubmenu::make();

echo FormContainer::make()
    ->title(Yii::t('config', 'Update Settings'))
    ->form(ConfigActiveForm::make()
        ->model($config));
