<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin\Models;

use Hirtz\Config\Modules\Admin\Module;
use Hirtz\Skeleton\Base\Traits\ModelTrait;
use Hirtz\Skeleton\Behaviors\TrailBehavior;
use Hirtz\Skeleton\Helpers\FileHelper;
use Hirtz\Skeleton\Models\Interfaces\TrailModelInterface;
use Hirtz\Skeleton\Models\Traits\AdminModelTrait;
use Hirtz\Skeleton\Models\Traits\I18nAttributesTrait;
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
    use AdminModelTrait;
    use I18nAttributesTrait;
    use ModelTrait;
    use TrailModelTrait;

    public const string AUTH_CONFIG_UPDATE = 'configUpdate';

    protected static ?Module $module = null;

    /** @var array<string, mixed> */
    private array $attributes = [];

    /** @var array<string, mixed>|null */
    private ?array $params = null;

    #[Override]
    public function __get($name): mixed
    {
        if (in_array($name, $this->activeAttributes())) {
            return $this->attributes[$name] ?? null;
        }

        return parent::__get($name);
    }

    #[Override]
    public function __set($name, $value): void
    {
        if (in_array($name, $this->activeAttributes())) {
            $this->attributes[$name] = $value;
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

    /**
     * @param string[]|null $attributeNames
     */
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

        $username = Yii::$app->has('user') ? Yii::$app->getUser()->getIdentity()?->getUsername() : null;

        $phpdoc = $username ? "Last updated via administration by $username" : null;
        $file = $this->getConfigFilePath();

        FileHelper::createDirectory(dirname($file));
        FileHelper::createConfigFile($file, $params, $phpdoc);

        Yii::$app->params = [...Yii::$app->params, ...$params];
        $this->params = null;

        $this->afterSave(array_intersect_key($prevParams, $changedAttributes));

        return true;
    }

    /**
     * Triggers an {@see BaseActiveRecord::EVENT_AFTER_UPDATE} so TrailBehavior can hook to it.
     */
    /**
     * @param array<string, mixed> $changedAttributes
     */
    protected function afterSave(array $changedAttributes): void
    {
        $this->trigger(BaseActiveRecord::EVENT_AFTER_UPDATE, new AfterSaveEvent([
            'changedAttributes' => $changedAttributes,
        ]));
    }

    /**
     * @return list<string>|false
     */
    public function getAdminRoute(): array|false
    {
        return ['/admin/config/update'];
    }

    public function getAdminType(): string
    {
        return Yii::t('config', 'IN_CONFIG');
    }

    public function getUpdatedAt(): false|int
    {
        $file = $this->getConfigFilePath();
        return is_file($file) ? filemtime($file) : false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getParams(): array
    {
        if (null === $this->params) {
            $file = $this->getConfigFilePath();
            $this->params = is_file($file) ? require($file) : [];
        }

        return $this->params;
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
        static::$module ??= $module;

        return static::$module;
    }
}
