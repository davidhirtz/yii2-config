<?php

declare(strict_types=1);

namespace Hirtz\Config\Migrations;

use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\Models\User;
use Yii;
use yii\db\Migration;

/**
 * The permission names and descriptions this creates are hardcoded: `M2609141[0-6]0000AuthItems` collapses them
 * into one permission per model, so neither the constants nor the message keys exist any more.
 *
 * @noinspection PhpUnused
 */

class M220207124544Config extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        $auth = Yii::$app->getAuthManager();
        $admin = $auth->getRole(User::AUTH_ROLE_ADMIN);

        $configUpdate = $auth->createPermission('configUpdate');
        $configUpdate->description = 'Update website settings';
        $auth->add($configUpdate);

        $auth->addChild($admin, $configUpdate);
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->getAuthManager();
        $this->delete($auth->itemTable, ['name' => 'configUpdate']);
    }
}
