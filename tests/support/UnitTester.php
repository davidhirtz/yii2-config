<?php

declare(strict_types=1);

namespace davidhirtz\yii2\config\tests\support;

use davidhirtz\yii2\skeleton\models\Trail;

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
