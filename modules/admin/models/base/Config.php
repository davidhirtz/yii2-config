<?php


namespace davidhirtz\yii2\config\modules\admin\models\base;

use davidhirtz\yii2\config\modules\admin\Module;
use davidhirtz\yii2\config\modules\admin\widgets\forms\ConfigActiveForm;
use davidhirtz\yii2\skeleton\helpers\FileHelper;
use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;

/**
 * Class Config
 * @package davidhirtz\yii2\config\modules\admin\models\base
 * @see \davidhirtz\yii2\config\modules\admin\models\Config
 */
class Config extends Model
{
    public const AUTH_CONFIG_UPDATE = 'configUpdate';

    /**
     * @var Module
     */
    protected static $_module;

    /**
     * @var array
     */
    private $_params;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->setAttributesFromParams();
        parent::init();
    }

    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'TrailBehavior' => [
                'class' => 'davidhirtz\yii2\skeleton\behaviors\TrailBehavior',
            ],
        ]);
    }

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
        $params = $prevParams;

        foreach ($this->activeAttributes() as $attribute) {
            $params[$attribute] = $this->$attribute;
        }

        if ($changedAttributes = array_diff_assoc($params, $prevParams)) {
            $phpdoc = (Yii::$app->has('user') && $username = (Yii::$app->getUser()->getIdentity()->getUsername() ?? false)) ? "Last updated via administration by {$username}" : null;
            FileHelper::createConfigFile(static::getModule()->configFile, $params, $phpdoc);

            $this->afterSave(array_intersect_key($prevParams, $changedAttributes));
            return true;
        }

        return false;
    }

    /**
     * Triggers an {@link ActiveRecord::EVENT_AFTER_UPDATE} so TrailBehavior can hook to it.
     *
     * @param array $changedAttributes
     * @return void
     */
    public function afterSave($changedAttributes)
    {
        $this->trigger(ActiveRecord::EVENT_AFTER_UPDATE, new AfterSaveEvent([
            'changedAttributes' => $changedAttributes,
        ]));
    }

    /**
     * @return string
     */
    public function getTrailModelName()
    {
        return static::getModule()->name;
    }

    /**
     * @return array
     */
    public function getTrailModelAdminRoute()
    {
        return ['/admin/config/update'];
    }

    /**
     * @return false|int|null
     */
    public function getUpdatedAt()
    {
        $file = Yii::getAlias(static::getModule()->configFile);
        return is_file($file) ? filemtime($file) : null;
    }

    /**
     * @return array
     */
    protected function getParams()
    {
        if ($this->_params === null) {
            $file = Yii::getAlias(static::getModule()->configFile);
            $this->_params = is_file($file) ? require($file) : [];
        }

        return $this->_params;
    }

    /**
     * @return void
     */
    protected function setAttributesFromParams()
    {
        $this->setAttributes($this->getParams(), false);
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
