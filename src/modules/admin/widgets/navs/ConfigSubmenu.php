<?php

declare(strict_types=1);

namespace Hirtz\Config\modules\admin\widgets\navs;

use Hirtz\Config\modules\admin\models\Config;
use Hirtz\Skeleton\widgets\fontawesome\Submenu;

class ConfigSubmenu extends Submenu
{
    public function init(): void
    {
        $this->title ??= Config::getModule()->getName();
        parent::init();
    }
}
