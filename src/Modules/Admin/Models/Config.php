<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin\Models;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Config\Modules\Admin\Module;
use Hirtz\Skeleton\Base\Traits\ModelTrait;
use Hirtz\Skeleton\Behaviors\TrailBehavior;
use Hirtz\Skeleton\Helpers\FileHelper;
use Hirtz\Skeleton\Models\Interfaces\TrailModelInterface;
use Hirtz\Skeleton\Models\Traits\I18nAttributesTrait;
use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Models\Traits\TrailModelTrait;
use Override;
use Yii;
use yii\base\Model;
use yii\db\AfterSaveEvent;
use yii\db\BaseActiveRecord;

/**
 * Extend this class in your application to make application params editable via the admin interface. Params must have
 * a valid rule. Only active attributes will be displayed in the form.
 */
class Config extends Model implements TrailModelInterface
{
    use I18nAttributesTrait;
    use ModelTrait;
    use TrailModelTrait;

    public const string AUTH_CONFIG_UPDATE = 'configUpdate';

    protected static ?Module $_module = null;

    private array $_attributes = [];
    private ?array $_params = null;

    #[Override]
    public function __get($name): mixed
    {
        if (in_array($name, $this->activeAttributes())) {
            return $this->_attributes[$name] ?? null;
        }

        return parent::__get($name);
    }

    #[Override]
    public function __set($name, $value): void
    {
        if (in_array($name, $this->activeAttributes())) {
            $this->_attributes[$name] = $value;
            return;
        }

        parent::__set($name, $value);
    }

    #[Override]
    public function attributes(): array
    {
        return $this->activeAttributes();
    }

    #[Override]
    public function behaviors(): array
    {
        return [...parent::behaviors(), 'TrailBehavior' => [
            'class' => TrailBehavior::class,
        ]];
    }

    public function init(): void
    {
        $this->setAttributesFromParams();
        parent::init();
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
            $prevParams[$attribute] ??= null;
            $params[$attribute] = $this->$attribute;
        }

        $changedAttributes = array_diff_assoc($params, $prevParams);

        if (!$changedAttributes) {
            return false;
        }

        $identity = Yii::$app->has('user') ? Yii::$app->getUser()->getIdentity() : null;
        $username = $identity instanceof User ? $identity->getUsername() : null;

        $phpdoc = $username ? "Last updated via administration by $username" : null;
        $file = $this->getConfigFilePath();

        FileHelper::createDirectory(dirname($file));
        FileHelper::createConfigFile($file, $params, $phpdoc);

        Yii::$app->params = [...Yii::$app->params, ...$params];
        $this->_params = null;

        $this->afterSave(array_intersect_key($prevParams, $changedAttributes));

        return true;
    }

    /**
     * Triggers an {@see BaseActiveRecord::EVENT_AFTER_UPDATE} so TrailBehavior can hook to it.
     */
    protected function afterSave(array $changedAttributes): void
    {
        $this->trigger(BaseActiveRecord::EVENT_AFTER_UPDATE, new AfterSaveEvent([
            'changedAttributes' => $changedAttributes,
        ]));
    }

    public function getTrailModelName(): string
    {
        return Lang::t('config', 'IN_CONFIG');
    }

    public function getAdminRoute(): array|false
    {
        return ['/admin/config/update'];
    }

    public function getUpdatedAt(): false|int
    {
        $file = $this->getConfigFilePath();
        return is_file($file) ? filemtime($file) : false;
    }

    protected function getParams(): array
    {
        if (null === $this->_params) {
            $file = $this->getConfigFilePath();
            $this->_params = is_file($file) ? require($file) : [];
        }

        return $this->_params;
    }

    protected function setAttributesFromParams(): void
    {
        $this->setAttributes($this->getParams());
    }

    protected function getConfigFilePath(): string
    {
        $file = Yii::getAlias(static::getModule()->configFile);

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }

        return $file;
    }

    public static function getModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('admin')->getModule('config');
        static::$_module ??= $module;

        return static::$_module;
    }
}
