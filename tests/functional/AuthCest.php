<?php

/**
 * @noinspection PhpUnused
 */

declare(strict_types=1);

/**
 * @noinspection PhpUnused
 */

namespace davidhirtz\yii2\config\tests\functional;

use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\config\modules\admin\widgets\forms\ConfigActiveForm;
use davidhirtz\yii2\config\tests\support\FunctionalTester;
use davidhirtz\yii2\skeleton\codeception\fixtures\UserFixtureTrait;
use davidhirtz\yii2\skeleton\codeception\functional\BaseCest;
use davidhirtz\yii2\skeleton\models\User;
use davidhirtz\yii2\skeleton\modules\admin\widgets\forms\LoginActiveForm;
use Yii;

class AuthCest extends BaseCest
{
    use UserFixtureTrait;

    public function checkAdminUrlAsGuest(FunctionalTester $I): void
    {
        $I->amOnPage($this->getAdminUrl());

        $widget = Yii::createObject(LoginActiveForm::class);
        $I->seeElement("#$widget->id");
    }

    public function checkAdminUrlWithoutPermission(FunctionalTester $I): void
    {
        $this->getLoggedInUser();

        $I->amOnPage($this->getAdminUrl());
        $I->seeResponseCodeIs(403);
    }

    public function checkAdminUrlWithPermission(FunctionalTester $I): void
    {
        $user = $this->getLoggedInUser();
        $auth = Yii::$app->getAuthManager()->getPermission(Config::AUTH_CONFIG_UPDATE);
        Yii::$app->getAuthManager()->assign($auth, $user->id);

        $widget = Yii::$container->get(ConfigActiveForm::class, [], [
            'model' => Config::instance(),
        ]);

        $I->amOnPage($this->getAdminUrl());
        $I->seeElement("#$widget->id");
    }

    protected function getAdminUrl(): string
    {
        return Yii::$app->getUrlManager()->createUrl(Config::instance()->getAdminRoute());
    }

    protected function getLoggedInUser(): User
    {
        $webuser = Yii::$app->getUser();
        $webuser->loginType = 'test';

        $user = User::find()->one();
        $webuser->login($user);

        return $user;
    }
}
