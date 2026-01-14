<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin\Widgets\Navs;

use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Skeleton\Widgets\Navs\Submenu;

class ConfigSubmenu extends Submenu
{
    #[\Override]
    protected function configure(): void
    {
        $this->title ??= Config::getModule()->getName();
        parent::configure();
    }
}
