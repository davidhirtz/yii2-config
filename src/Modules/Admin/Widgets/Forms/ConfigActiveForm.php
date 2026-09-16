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
        return array_map(static fn (string $attribute): array => [$attribute], $this->model->activeAttributes());
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
