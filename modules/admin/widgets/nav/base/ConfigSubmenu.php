<?php

namespace davidhirtz\yii2\config\modules\admin\widgets\nav\base;

use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Submenu;
use Yii;

/**
 * Class ConfigSubmenu
 * @package davidhirtz\yii2\config\modules\admin\widgets\nav\base
 */
class ConfigSubmenu extends Submenu
{
    /**
     * @inheritDoc
     */
    public function init(): void
    {
        if (!$this->title) {
            $this->title = Config::getModule()->name;
        }

        parent::init();
    }
}