<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin;

use Hirtz\Cms\Models\Entry;
use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Config\Modules\Admin\Widgets\Navs\ConfigNavItem;
use Hirtz\Skeleton\Modules\Admin\ModuleInterface;
use Hirtz\Skeleton\Modules\Admin\Widgets\Navs\SystemNavItem;
use Hirtz\Skeleton\Widgets\Navs\Nav;
use Hirtz\Skeleton\Widgets\Navs\NavItem;
use Hirtz\Skeleton\Widgets\Panels\Dashboard;
use Hirtz\Skeleton\Widgets\Panels\DashboardItem;
use Override;
use Yii;

/**
 * @property \Hirtz\Skeleton\Modules\Admin\Module $module
 */
class Module extends \Hirtz\Skeleton\Base\Module implements ModuleInterface
{
    public $defaultRoute = 'config';
    public string $configFile = '@root/config/params.php';

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

    public function dashboard(Dashboard $dashboard): Dashboard
    {
        return $dashboard->addItem(DashboardItem::make()
            ->icon('pen')
            ->label(Yii::t('config', 'COMMON_SETTINGS'))
            ->roles([Config::AUTH_CONFIG_UPDATE])
            ->url(['/admin/config/config/update']));
    }
}
