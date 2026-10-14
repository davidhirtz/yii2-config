<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin\Models;

use ArrayObject;
use Hirtz\Config\Modules\Admin\Module;
use Hirtz\Skeleton\Base\Traits\ModelTrait;
use Hirtz\Skeleton\Behaviors\TrailBehavior;
use Hirtz\Skeleton\Filters\PageCache;
use Hirtz\Skeleton\Helpers\ConfigFile;
use Hirtz\Skeleton\Models\Interfaces\TrailModelInterface;
use Hirtz\Skeleton\Models\Traits\AdminModelTrait;
use Hirtz\Skeleton\Models\Traits\I18nAttributesTrait;
use Hirtz\Skeleton\Models\Traits\TrailModelTrait;
use Hirtz\Skeleton\Validators\SensitiveAttributeValidator;
use Hirtz\Skeleton\Web\User as WebUser;
use Override;
use Yii;
use yii\base\Model;
use yii\caching\TagDependency;
use yii\db\AfterSaveEvent;
use yii\db\BaseActiveRecord;
use yii\validators\Validator;

/**
 * Extend this class in your application to make application params editable via the admin interface. Params must have
 * a valid rule. Only active attributes will be displayed in the form.
 */
class Config extends Model implements TrailModelInterface
{
    use AdminModelTrait;
    use I18nAttributesTrait;
    use ModelTrait {
        createValidators as private createModelValidators;
    }
    use TrailModelTrait;

    public const string AUTH_CONFIG = 'config';

    /**
     * @var list<string> the params the bundles read as secrets: an editable one is validated by
     * {@see SensitiveAttributeValidator} without a rule naming it, so it renders as a password field and, like any
     * attribute that validator names, stays out of the trail.
     */
    public array $sensitiveParams = [
        'cookieValidationKey',
        'googleApiKey',
        'mailerDsn',
        'passwordPepper',
        'registryKey',
        'secretKey',
        'sentryDsn',
        'shopifyAccessToken',
        'shopifyApiKey',
        'shopifyApiSecret',
        'shopifyStorefrontAccessToken',
    ];

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

    public function createValidators(): ArrayObject
    {
        $validators = $this->createModelValidators();
        $attributes = [];

        foreach ($validators as $validator) {
            $attributes = [...$attributes, ...$validator->getAttributeNames()];
        }

        $sensitive = array_values(array_intersect($this->sensitiveParams, $attributes));

        if ($sensitive) {
            $validators->append(Validator::createValidator(SensitiveAttributeValidator::class, $this, $sensitive));
        }

        return $validators;
    }

    /**
     * @return list<string>
     */
    public function getTrailAttributes(): array
    {
        return array_values(array_diff(
            $this->attributes(),
            $this->getTrailBehavior()->exclude,
            $this->getSensitiveAttributes(),
        ));
    }

    /**
     * @return list<string>
     */
    public function getSensitiveAttributes(): array
    {
        $attributes = [];

        foreach ($this->getValidators() as $validator) {
            if ($validator instanceof SensitiveAttributeValidator) {
                $attributes = [...$attributes, ...$validator->getAttributeNames()];
            }
        }

        return array_values(array_unique($attributes));
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

        $changedAttributes = [];

        foreach ($this->activeAttributes() as $attribute) {
            if (!$this->isSameValue($prevParams[$attribute], $params[$attribute])) {
                $changedAttributes[$attribute] = $params[$attribute];
            }
        }

        if (!$changedAttributes) {
            return false;
        }

        $username = WebUser::current()?->getIdentity()?->getUsername();

        $phpdoc = $username ? "Last updated via administration by $username" : null;
        ConfigFile::write($this->getConfigFilePath(), $params, $phpdoc);

        Yii::$app->params = [...Yii::$app->params, ...$params];
        $this->params = null;

        if ($cache = Yii::$app->getCache()) {
            TagDependency::invalidate($cache, PageCache::TAG_DEPENDENCY_KEY);
        }

        $this->afterSave(array_intersect_key($prevParams, $changedAttributes));

        return true;
    }

    /**
     * A form posts strings, so a scalar compares as one — `'1'` is the `1` the file holds. Only the form's own
     * attributes are compared: whatever else the file holds, an array written by hand among it, stays as it is.
     */
    protected function isSameValue(mixed $old, mixed $new): bool
    {
        if (is_array($old) || is_array($new) || is_object($old) || is_object($new)) {
            return $old === $new;
        }

        return (string)$old === (string)$new;
    }

    /**
     * Triggers an {@see BaseActiveRecord::EVENT_AFTER_UPDATE} so TrailBehavior can hook to it.
     *
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
        return ['/admin/config/config/update'];
    }

    public function getPermissionName(): string
    {
        return self::AUTH_CONFIG;
    }

    public function getAdminType(): string
    {
        return Yii::t('config', 'CONFIG_NAME');
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
        return $module;
    }
}
