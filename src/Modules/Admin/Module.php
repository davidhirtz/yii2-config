<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin;

use Hirtz\Config\Modules\Admin\Widgets\Navs\ConfigNavItem;
use Hirtz\Skeleton\Modules\Admin\ModuleInterface;
use Hirtz\Skeleton\Modules\Admin\Widgets\Navs\SystemNavItem;
use Hirtz\Skeleton\Widgets\Navs\Nav;
use Override;

/**
 * @property \Hirtz\Skeleton\Modules\Admin\Module $module
 */
class Module extends \Hirtz\Skeleton\Base\Module implements ModuleInterface
{
    public $defaultRoute = 'config';
    public string $configFile = '@root/config/params.php';

    public function getDashboardPanels(): array
    {
        return [];
    }

    #[Override]
    public function aside(Nav $nav): Nav
    {
        return $nav->items(function (array $items): array {
            foreach ($items as $item) {
                if ($item instanceof SystemNavItem) {
                    $item->addItem(ConfigNavItem::make());
                    break;
                }
            }

            return $items;
        });
    }
}
