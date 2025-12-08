<?php

declare(strict_types=1);

namespace Hirtz\Config\tests\support;

use Hirtz\Skeleton\Models\Trail;

class UnitTester extends \Codeception\Actor
{
    use _generated\UnitTesterActions;
    use traits\ConfigTesterTrait;

    public function loadLastTrail(): ?Trail
    {
        return Trail::find()
            ->orderBy(['id' => SORT_DESC])
            ->one();
    }
}
