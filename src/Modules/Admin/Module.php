<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin;

use Hirtz\Config\Modules\Admin\Controllers\ConfigController;
use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Skeleton\Modules\Admin\ModuleInterface;
use Hirtz\Skeleton\Modules\Admin\Widgets\Navs\SystemNavItem;
use Hirtz\Skeleton\Widgets\Navs\Nav;
use Hirtz\Skeleton\Widgets\Navs\NavItem;
use Override;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property \Hirtz\Skeleton\Modules\Admin\Module $module
 */
class Module extends \Hirtz\Skeleton\Base\Module implements ModuleInterface
{
    public string $configFile = '@root/config/params.php';
    public array|string $url = ['/admin/config/update'];

    #[Override]
    public function init(): void
    {
        $this->controllerMap = ArrayHelper::merge($this->getCoreControllerMap(), $this->controllerMap);
        parent::init();
    }

    protected function getCoreControllerMap(): array
    {
        return [
            'config' => [
                'class' => ConfigController::class,
                'viewPath' => '@config/../resources/views/admin/config',
            ],
        ];
    }

    public function getDashboardPanels(): array
    {
        return [];
    }

    public function getName(): string
    {
        return Yii::t('config', 'Settings');
    }

    public function aside(Nav $nav): Nav
    {
        return $nav->items(function (array $items): array {
            foreach ($items as $item) {
                if ($item instanceof SystemNavItem) {
                    $item->roles([Config::AUTH_CONFIG_UPDATE])
                        ->addItem(NavItem::make()
                            ->label($this->getName())
                            ->url($this->url)
                            ->roles([Config::AUTH_CONFIG_UPDATE])
                            ->routes(['admin/config/'])
                            ->order(100));
                }
            }

            return $items;
        });
    }
}
