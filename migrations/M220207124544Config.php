<?php

namespace davidhirtz\yii2\config\migrations;

use davidhirtz\yii2\skeleton\db\MigrationTrait;
use davidhirtz\yii2\skeleton\models\User;
use Yii;
use yii\db\Migration;

/**
 * Class M220207124544Config
 * @package davidhirtz\yii2\config\migrations
 * @noinspection PhpUnused
 */
class M220207124544Config extends Migration
{
    use MigrationTrait;

    /**
     * @inheritDoc
     */
    public function safeUp()
    {
        $sourceLanguage = Yii::$app->sourceLanguage;

        $auth = Yii::$app->getAuthManager();
        $admin = $auth->getRole(User::AUTH_ROLE_ADMIN);

        $configUpdate = $auth->createPermission('configUpdate');
        $configUpdate->description = Yii::t('config', 'Update website settings', [], $sourceLanguage);
        $auth->add($configUpdate);

        $auth->addChild($admin, $configUpdate);
    }

    /**
     * @inheritDoc
     */
    public function safeDown()
    {
        $auth = Yii::$app->getAuthManager();
        $this->delete($auth->itemTable, ['name' => 'configUpdate']);
    }
}