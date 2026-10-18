<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin\Widgets\Forms;

use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Skeleton\Helpers\Html;
use Hirtz\Skeleton\Html\Checkbox;
use Hirtz\Skeleton\Html\Div;
use Hirtz\Skeleton\Html\Label;
use Hirtz\Skeleton\Widgets\Forms\ActiveForm;
use Hirtz\Skeleton\Widgets\Forms\Fields\InputField;
use Hirtz\Skeleton\Widgets\Forms\FormRow;
use Hirtz\Skeleton\Widgets\Forms\Footers\UpdatedAtFooterItem;
use Stringable;
use Yii;

/**
 * @property Config $model
 */
class ConfigActiveForm extends ActiveForm
{
    #[\Override]
    protected function configure(): void
    {
        $this->footer ??= [
            $this->getUpdatedAtFooterItem(),
        ];

        parent::configure();
    }

    #[\Override]
    protected function getDefaultRows(): array
    {
        $rows = [];

        foreach ($this->model->activeAttributes() as $attribute) {
            if ($this->model->isSensitiveAttribute($attribute)) {
                $rows[] = [$this->getSensitiveField($attribute)];

                if ($remove = $this->getRemoveCheckbox($attribute)) {
                    $rows[] = [$remove];
                }

                continue;
            }

            $rows[] = [$attribute];
        }

        return $rows;
    }

    /**
     * Never filled in: the saved value shows only by its first characters, and an empty field keeps it.
     */
    protected function getSensitiveField(string $attribute): Stringable
    {
        $masked = $this->model->getMaskedValue($attribute);

        return InputField::make()
            ->property($attribute)
            ->type('password')
            ->value('')
            ->attribute('autocomplete', 'new-password')
            ->attribute('placeholder', $masked)
            ->hint($masked !== null ? Yii::t('config', 'CONFIG_SENSITIVE_HINT') : null);
    }

    protected function getRemoveCheckbox(string $attribute): ?Stringable
    {
        if ($this->model->getMaskedValue($attribute) === null) {
            return null;
        }

        $id = Html::getInputId($this->model, $attribute) . '-remove';

        return FormRow::make()
            ->content(Div::make()
                ->addClass('form-checkbox')
                ->content(
                    Div::make()
                        ->addClass('checkbox')
                        ->content(Checkbox::make()
                            ->addClass('input')
                            ->attribute('id', $id)
                            ->name(Html::getInputName($this->model, 'removedAttributes') . '[]')
                            ->value($attribute)),
                    Label::make()
                        ->addClass('label')
                        ->for($id)
                        ->text(Yii::t('config', 'CONFIG_SENSITIVE_REMOVE')),
                ));
    }

    protected function getUpdatedAtFooterItem(): ?Stringable
    {
        $value = $this->model->getUpdatedAt();

        return $value
            ? UpdatedAtFooterItem::make()
                ->model($this->model)
                ->value($value)
            : null;
    }
}
