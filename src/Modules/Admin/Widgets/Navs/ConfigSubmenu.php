<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin\Widgets\Navs;

use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Skeleton\Widgets\Fontawesome\Submenu;

class ConfigSubmenu extends Submenu
{
    public function init(): void
    {
        $this->title ??= Config::getModule()->getName();
        parent::init();
    }
}
