<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin\Widgets\Forms\Fields;

use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Skeleton\Helpers\Html;
use Hirtz\Skeleton\Html\Base\Tag;
use Hirtz\Skeleton\Html\Input;
use Hirtz\Skeleton\Html\Span;
use Hirtz\Skeleton\Widgets\Buttons\Button;
use Hirtz\Skeleton\Widgets\Forms\Fields\InputField;
use Hirtz\Skeleton\Widgets\Forms\InputGroup;
use Override;
use Stringable;
use Yii;

/**
 * A saved secret is never sent back: the field shows it masked and swaps itself through
 * {@see \Hirtz\Config\Modules\Admin\Controllers\ConfigController::actionSensitive()}, under a row id derived from the
 * input name, since the swap spans two requests.
 */
class SensitiveField extends InputField
{
    final public const string STATE_SAVED = 'saved';
    final public const string STATE_REPLACE = 'replace';
    final public const string STATE_REMOVE = 'remove';

    protected ?string $state = null;

    public function state(?string $state): static
    {
        $this->state = $state;
        return $this;
    }

    #[Override]
    protected function configure(): void
    {
        $this->attributes['type'] = 'password';
        $this->attributes['value'] = '';
        $this->attributes['autocomplete'] ??= 'new-password';

        parent::configure();

        $this->rowAttributes['id'] ??= $this->getRowId();
        $this->state = $this->getMaskedValue() === null ? null : ($this->state ?? $this->getDefaultState());

        if ($this->state === self::STATE_REPLACE) {
            $this->attributes['placeholder'] ??= $this->getMaskedValue();
            $this->hint = $this->hint ?: Yii::t('config', 'CONFIG_SENSITIVE_HINT');
        }
    }

    /**
     * After a failed save a removal stays asked for, and a value typed is asked for again rather than dropped unseen.
     */
    protected function getDefaultState(): string
    {
        $model = $this->model;
        $property = (string)$this->property;

        if (!$model instanceof Config) {
            return self::STATE_SAVED;
        }

        if ($model->isRemovedAttribute($property)) {
            return self::STATE_REMOVE;
        }

        return $model->hasErrors($property) || $model->isChangedAttribute($property)
            ? self::STATE_REPLACE
            : self::STATE_SAVED;
    }

    #[Override]
    protected function getInput(): string|Stringable
    {
        return match ($this->state) {
            self::STATE_SAVED => InputGroup::make()
                ->append(
                    $this->getStateButton(self::STATE_REPLACE, 'pencil-alt', Yii::t('config', 'CONFIG_SENSITIVE_REPLACE')),
                    $this->getStateButton(self::STATE_REMOVE, 'trash', Yii::t('config', 'CONFIG_SENSITIVE_REMOVE')),
                )
                ->content(Span::make()
                    ->addClass('input text-muted')
                    ->text((string)$this->getMaskedValue())),
            self::STATE_REPLACE => InputGroup::make()
                ->append($this->getUndoButton())
                ->content(parent::getInput()),
            self::STATE_REMOVE => InputGroup::make()
                ->append($this->getUndoButton())
                ->content(
                    Span::make()
                        ->addClass('input text-muted')
                        ->text(Yii::t('config', 'CONFIG_SENSITIVE_REMOVED')),
                    $this->getRemovedInput(),
                ),
            default => parent::getInput(),
        };
    }

    /**
     * Only the password input is labelable.
     */
    #[Override]
    protected function getLabel(): ?Tag
    {
        $label = parent::getLabel();

        return $this->state === self::STATE_SAVED || $this->state === self::STATE_REMOVE
            ? $label?->attribute('for', null)
            : $label;
    }

    protected function getRemovedInput(): string|Stringable
    {
        return $this->model instanceof Config
            ? Input::make()
                ->type('hidden')
                ->name(Html::getInputName($this->model, 'removedAttributes') . '[]')
                ->value((string)$this->property)
            : '';
    }

    protected function getUndoButton(): Stringable
    {
        return $this->getStateButton(self::STATE_SAVED, 'rotate-left', Yii::t('config', 'CONFIG_SENSITIVE_UNDO'));
    }

    protected function getStateButton(string $state, string $icon, string $tooltip): Stringable
    {
        return Button::make()
            ->class('btn')
            ->icon($icon)
            ->tooltip($tooltip)
            ->replace([
                '/admin/config/config/sensitive',
                'attribute' => $this->property,
                'state' => $state,
            ], '#' . $this->getRowId());
    }

    protected function getMaskedValue(): ?string
    {
        return $this->model instanceof Config ? $this->model->getMaskedValue((string)$this->property) : null;
    }

    protected function getRowId(): string
    {
        return $this->getId() . '-row';
    }
}
