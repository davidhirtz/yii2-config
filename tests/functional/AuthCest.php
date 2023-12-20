<?php

/**
 * @noinspection PhpUnused
 */

namespace davidhirtz\yii2\config\tests\functional;

use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\config\modules\admin\widgets\forms\ConfigActiveForm;
use davidhirtz\yii2\config\tests\fixtures\UserFixture;
use davidhirtz\yii2\config\tests\support\FunctionalTester;
use davidhirtz\yii2\skeleton\db\Identity;
use davidhirtz\yii2\skeleton\models\User;
use davidhirtz\yii2\skeleton\modules\admin\widgets\forms\LoginActiveForm;
use Yii;

class AuthCest extends BaseCest
{
    public function _fixtures(): array
    {
        return [
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'user.php',
            ],
        ];
    }

    public function checkIndexAsGuest(FunctionalTester $I): void
    {
        $I->amOnPage('/admin/config/update');

        $widget = Yii::createObject(LoginActiveForm::class);
        $I->seeElement("#$widget->id");
    }

    public function checkIndexWithoutPermission(FunctionalTester $I): void
    {
        $this->getLoggedInUser();

        $I->amOnPage('/admin/config/update');
        $I->seeResponseCodeIs(403);
    }

    public function checkIndexWithPermission(FunctionalTester $I): void
    {
        $user = $this->getLoggedInUser();
        $auth = Yii::$app->getAuthManager()->getPermission(Config::AUTH_CONFIG_UPDATE);
        Yii::$app->getAuthManager()->assign($auth, $user->id);

        $widget = Yii::$container->get(ConfigActiveForm::class, [], [
            'model' => Config::instance(),
        ]);

        $I->amOnPage('/admin/config/update');
        $I->seeElement("#$widget->id");
    }

    protected function getLoggedInUser(): User
    {
        $user = Identity::find()->one();
        $user->loginType = 'test';

        Yii::$app->getUser()->login($user);
        return $user;
    }
}
