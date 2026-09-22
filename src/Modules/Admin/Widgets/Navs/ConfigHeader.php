<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin\Widgets\Navs;

use Hirtz\Skeleton\Widgets\Navs\Header;
use Override;
use Yii;

class ConfigHeader extends Header
{
    #[Override]
    protected function configure(): void
    {
        $this->title ??= Yii::t('config', 'CONFIG_TITLE');

        $this->addSystemBreadcrumb();

        parent::configure();
    }

    /**
     * The settings live under the system nav item, not beside it.
     */
    protected function addSystemBreadcrumb(): void
    {
        $this->addBreadcrumb(Yii::t('skeleton', 'COMMON_SYSTEM'), ['/admin/system/index']);
    }
}
