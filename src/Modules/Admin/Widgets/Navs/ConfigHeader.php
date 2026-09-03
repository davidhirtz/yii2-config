<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin\Widgets\Navs;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Skeleton\Widgets\Navs\Header;
use Override;
use Yii;

class ConfigHeader extends Header
{
    #[Override]
    protected function configure(): void
    {
        $this->title ??= Lang::t('config', 'COMMON_SETTINGS');
        parent::configure();
    }
}
