<?php

declare(strict_types=1);

namespace Hirtz\Config\tests\support\Traits;

use Hirtz\Config\Modules\Admin\Models\Config;
use Yii;
use yii\helpers\FileHelper;

trait ConfigTesterTrait
{
    private ?string $_configFile = null;

    public function deleteConfigFile(): void
    {
        $file = Yii::getAlias($this->getConfigFile());
        FileHelper::removeDirectory(dirname($file));
    }

    public function getConfigFile(): string
    {
        return $this->_configFile ??= Config::getModule()->configFile;
    }
}
