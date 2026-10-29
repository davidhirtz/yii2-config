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
use yii\validators\BooleanValidator;
use yii\validators\NumberValidator;
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

    /** @var array<string, mixed> */
    private array $attributes = [];

    /** @var array<string, mixed>|null */
    private ?array $params = null;

    /**
     * @var list<string> sensitive attributes the form asked to remove, which an empty field cannot say
     */
    private array $removedAttributes = [];

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

        // A secret a bundle declared is masked whether or not the project's rule says so
        $sensitive = array_values(array_intersect(Yii::$app->sensitiveParams, $attributes));

        if ($sensitive) {
            $validators->append(Validator::createValidator(SensitiveAttributeValidator::class, $this, $sensitive));
        }

        return $validators;
    }

    #[Override]
    public function attributes(): array
    {
        return $this->activeAttributes();
    }

    /**
     * The form never shows a secret, so an empty field keeps the saved one; `removedAttributes` lists those to clear,
     * which a value typed into the same field overrules.
     *
     * @param array<array-key, mixed> $data
     */
    #[Override]
    public function load($data, $formName = null): bool
    {
        $scope = $formName ?? $this->formName();
        $values = $scope === '' ? $data : ($data[$scope] ?? null);

        if (is_array($values)) {
            $removed = is_array($values['removedAttributes'] ?? null) ? $values['removedAttributes'] : [];
            $this->removedAttributes = array_values(array_filter($removed, fn (mixed $name): bool => is_string($name)
                && $this->isSensitiveAttribute($name)
                && in_array($values[$name] ?? null, ['', null], true)));

            foreach ($values as $name => $value) {
                if (is_string($name) && ($value === '' || $value === null) && $this->isSensitiveAttribute($name)) {
                    unset($values[$name]);
                }
            }

            unset($values['removedAttributes']);
            $data = $scope === '' ? $values : [...$data, $scope => $values];
        }

        return parent::load($data, $formName);
    }

    public function isSensitiveAttribute(string $name): bool
    {
        foreach ($this->getActiveValidators($name) as $validator) {
            if ($validator instanceof SensitiveAttributeValidator) {
                return true;
            }
        }

        return false;
    }

    public function isRemovedAttribute(string $name): bool
    {
        return in_array($name, $this->removedAttributes, true);
    }

    public function isChangedAttribute(string $name): bool
    {
        return !$this->isSameValue(
            $this->typecastValue($name, $this->getParams()[$name] ?? null),
            $this->typecastValue($name, $this->$name),
        );
    }

    /**
     * The first characters of a saved secret, enough to tell which one it is; a short one shows none.
     */
    public function getMaskedValue(string $name): ?string
    {
        $value = $this->getParams()[$name] ?? null;

        if (!is_scalar($value) || (string)$value === '') {
            return null;
        }

        $value = (string)$value;
        return (mb_strlen($value) > 12 ? mb_substr($value, 0, 4) : '') . '••••••••';
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

        foreach ($this->removedAttributes as $attribute) {
            $this->$attribute = null;
        }

        $prevParams = $this->getParams();
        $params = $prevParams;

        foreach ($this->activeAttributes() as $attribute) {
            $prevParams[$attribute] ??= null;
            $params[$attribute] = $this->$attribute = $this->typecastValue($attribute, $this->$attribute);
        }

        $changedAttributes = [];

        foreach ($this->activeAttributes() as $attribute) {
            if (!$this->isSameValue($this->typecastValue($attribute, $prevParams[$attribute]), $params[$attribute])) {
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

        // A typed `false` is a value, which a param missing from the file is not
        if (is_bool($old) || is_bool($new)) {
            return $old === $new;
        }

        return (string)$old === (string)$new;
    }

    /**
     * A param is written as the type its rule names, a boolean as `bool`; one without a typed rule stays as posted.
     */
    protected function typecastValue(string $name, mixed $value): mixed
    {
        if (!is_scalar($value) || $value === '') {
            return $value;
        }

        foreach ($this->getActiveValidators($name) as $validator) {
            if ($validator instanceof BooleanValidator) {
                return $validator->trueValue === '1'
                    && $validator->falseValue === '0'
                    && in_array($value, [true, false, 0, 1, '0', '1'], true)
                        ? (bool)$value
                        : $value;
            }

            if ($validator instanceof NumberValidator && is_numeric($value)) {
                return $validator->integerOnly
                    ? (preg_match('/^\s*[+-]?\d+\s*$/', (string)$value) ? (int)$value : $value)
                    : (float)$value;
            }
        }

        return $value;
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
        return Yii::t('config', 'CONFIG_SINGULAR');
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
