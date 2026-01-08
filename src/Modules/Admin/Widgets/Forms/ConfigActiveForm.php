<?php

declare(strict_types=1);

namespace Hirtz\Config\Modules\Admin\Widgets\Forms;

use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Skeleton\Widgets\Forms\ActiveForm;
use Hirtz\Skeleton\Widgets\Forms\Footers\UpdatedAtFooterItem;
use Stringable;

/**
 * @property Config $model
 */
class ConfigActiveForm extends ActiveForm
{
    protected function configure(): void
    {
        $this->rows ??= array_map(fn ($attribute) => [$attribute], $this->model->activeAttributes());

        $this->footer ??= [
            $this->getUpdatedAtFooterItem(),
        ];

        parent::configure();
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
