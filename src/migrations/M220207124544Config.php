<?php

declare(strict_types=1);

namespace Hirtz\Config\migrations;

use Hirtz\Config\modules\admin\models\Config;
use Hirtz\Skeleton\db\traits\MigrationTrait;
use Hirtz\Skeleton\models\User;
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
        $configUpdate->description = Yii::t('config', 'Update website settings', [], $sourceLanguage);
        $auth->add($configUpdate);

        $auth->addChild($admin, $configUpdate);
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->getAuthManager();
        $this->delete($auth->itemTable, ['name' => Config::AUTH_CONFIG_UPDATE]);
    }
}
