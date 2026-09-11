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
use Hirtz\Config\Modules\Admin\Widgets\Navs\ConfigHeader;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Forms\FormContainer;

echo ConfigHeader::make();

echo FormContainer::make()
    ->form(ConfigActiveForm::make()
        ->model($config));
