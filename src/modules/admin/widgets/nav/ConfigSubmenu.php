<?php

namespace davidhirtz\yii2\config\modules\admin\widgets\nav;

use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Submenu;

class ConfigSubmenu extends Submenu
{
    public function init(): void
    {
        $this->title ??= Config::getModule()->name;
        parent::init();
    }
}