<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin\Widgets\Navs;

use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Skeleton\Widgets\Navs\NavItem;
use Yii;

class ConfigNavItem extends NavItem
{
    public function __construct(array $config = [])
    {
        $this->icon ??= 'wrench';
        $this->label ??= Yii::t('config', 'Settings');
        $this->order ??= 100;
        $this->roles ??= [Config::AUTH_CONFIG_UPDATE];
        $this->url ??= ['/admin/config/config/update'];

        parent::__construct($config);
    }
}
