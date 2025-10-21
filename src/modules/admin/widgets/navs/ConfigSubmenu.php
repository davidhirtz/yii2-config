<?php

declare(strict_types=1);

namespace davidhirtz\yii2\config\modules\admin\widgets\navs;

use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Submenu;
use Override;

class ConfigSubmenu extends Submenu
{
    #[Override]
    public function init(): void
    {
        $this->title ??= Config::getModule()->getName();
        parent::init();
    }
}
