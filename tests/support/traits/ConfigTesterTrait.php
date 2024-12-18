<?php

declare(strict_types=1);

namespace davidhirtz\yii2\config\tests\support\traits;

use davidhirtz\yii2\config\modules\admin\models\Config;
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
