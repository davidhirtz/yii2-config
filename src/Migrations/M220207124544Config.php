<?php

declare(strict_types=1);

namespace Hirtz\Config\Migrations;

use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\Models\User;
use Yii;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

class M220207124544Config extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        $sourceLanguage = Yii::$app->sourceLanguage;

        $auth = Yii::$app->getAuthManager();
        $admin = $auth->getRole(User::AUTH_ROLE_ADMIN);

        $configUpdate = $auth->createPermission(Config::AUTH_CONFIG_UPDATE);
        $configUpdate->description = Yii::t('config', 'AUTH_CONFIG_UPDATE_DESCRIPTION', [], $sourceLanguage);
        $auth->add($configUpdate);

        $auth->addChild($admin, $configUpdate);
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->getAuthManager();
        $this->delete($auth->itemTable, ['name' => Config::AUTH_CONFIG_UPDATE]);
    }
}
