<?php


namespace davidhirtz\yii2\config\modules\admin\models\base;

use davidhirtz\yii2\config\modules\admin\Module;
use davidhirtz\yii2\config\modules\admin\widgets\forms\ConfigActiveForm;
use davidhirtz\yii2\skeleton\helpers\FileHelper;
use Yii;
use yii\base\Model;

/**
 * Class Config
 * @package davidhirtz\yii2\config\modules\admin\models\base
 * @see \davidhirtz\yii2\config\modules\admin\models\Config
 */
class Config extends Model
{
    /**
     * @var Module
     */
    protected static $_module;

    /**
     * Saves the given all safe attributes to the config file.
     *
     * @param bool $runValidation
     * @param array|null $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        if ($runValidation && !$this->validate($attributeNames)) {
            Yii::info('Model not updated due to validation error.', __METHOD__);
            return false;
        }

        $prevParams = $this->getParams();
        $params = array_merge($prevParams, $this->activeAttributes());

        if (!array_diff_assoc($prevParams, $params)) {
            return false;
        }

        $phpdoc = (Yii::$app->has('user') && $username = (Yii::$app->getUser()->getIdentity()->getUsername() ?? false)) ? "Updated by {$username}" : null;
        FileHelper::createConfigFile(static::getModule()->configFile, $params, $phpdoc);
        return true;
    }

    /**
     * @return array
     */
    protected function getParams()
    {
        $file = Yii::getAlias(static::getModule()->configFile);
        return is_file($file) ? require($file) : [];
    }

    /**
     * @return ConfigActiveForm
     */
    public function getActiveForm()
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return ConfigActiveForm::class;
    }

    /**
     * @return Module
     */
    public static function getModule()
    {
        if (static::$_module === null) {
            static::$_module = Yii::$app->getModule('admin')->getModule('config');
        }

        return static::$_module;
    }
}
