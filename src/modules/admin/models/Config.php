<?php


namespace davidhirtz\yii2\config\modules\admin\models;

use davidhirtz\yii2\config\modules\admin\Module;
use davidhirtz\yii2\skeleton\behaviors\TrailBehavior;
use davidhirtz\yii2\skeleton\helpers\FileHelper;
use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;

/**
 * Extend this class in your application to make application params editable via the admin interface. Params need to be
 * defined as public properties and must have a valid rule. Per default, only active attributes will be displayed in
 * the form.
 */
class Config extends Model
{
    public const AUTH_CONFIG_UPDATE = 'configUpdate';

    protected static ?Module $_module = null;
    private ?array $_params = null;

    public function init(): void
    {
        $this->setAttributesFromParams();
        parent::init();
    }

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'TrailBehavior' => [
                'class' => TrailBehavior::class,
            ],
        ]);
    }

    public function save(bool $runValidation = true, ?array $attributeNames = null): bool
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
            $phpdoc = (Yii::$app->has('user') && $username = (Yii::$app->getUser()->getIdentity()->getUsername() ?? false)) ? "Last updated via administration by $username" : null;
            FileHelper::createConfigFile(static::getModule()->configFile, $params, $phpdoc);

            if (function_exists('opcache_invalidate')) {
                opcache_invalidate(Yii::getAlias(static::getModule()->configFile));
            }

            $this->afterSave(array_intersect_key($prevParams, $changedAttributes));
            return true;
        }

        return false;
    }

    /**
     * Triggers an {@link ActiveRecord::EVENT_AFTER_UPDATE} so TrailBehavior can hook to it.
     */
    public function afterSave(array $changedAttributes): void
    {
        $this->trigger(ActiveRecord::EVENT_AFTER_UPDATE, new AfterSaveEvent([
            'changedAttributes' => $changedAttributes,
        ]));
    }

    public function getTrailModelName(): string
    {
        return static::getModule()->name;
    }

    public function getTrailModelAdminRoute(): array|false
    {
        return ['/admin/config/update'];
    }

    public function getUpdatedAt(): bool|int|null
    {
        $file = Yii::getAlias(static::getModule()->configFile);
        return is_file($file) ? filemtime($file) : null;
    }

    protected function getParams(): array
    {
        if ($this->_params === null) {
            $file = Yii::getAlias(static::getModule()->configFile);
            $this->_params = is_file($file) ? require($file) : [];
        }

        return $this->_params;
    }

    protected function setAttributesFromParams(): void
    {
        $this->setAttributes($this->getParams(), false);
    }

    public static function getModule(): Module
    {
        static::$_module ??= Yii::$app->getModule('admin')->getModule('config');
        return static::$_module;
    }
}
