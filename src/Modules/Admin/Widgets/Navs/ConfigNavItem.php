<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin\Widgets\Navs;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Skeleton\Widgets\Navs\NavItem;

class ConfigNavItem extends NavItem
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        //$this->icon ??= 'wrench';
        $this->label ??= Lang::t('config', 'COMMON_SETTINGS');
        $this->order ??= 100;
        $this->roles ??= [Config::AUTH_CONFIG_UPDATE];
        $this->url ??= ['/admin/config/config/update'];

        parent::__construct($config);
    }
}
