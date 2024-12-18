<?php

declare(strict_types=1);

namespace davidhirtz\yii2\config\modules\admin\widgets\navs;

use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Submenu;

class ConfigSubmenu extends Submenu
{
    public function init(): void
    {
        $this->title ??= Config::getModule()->getName();
        parent::init();
    }
}
