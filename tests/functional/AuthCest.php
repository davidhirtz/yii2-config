<?php

/**
 * @noinspection PhpUnused
 */

declare(strict_types=1);

/**
 * @noinspection PhpUnused
 */

namespace Hirtz\Config\tests\functional;

use Hirtz\Config\modules\admin\models\Config;
use Hirtz\Config\modules\admin\widgets\forms\ConfigActiveForm;
use Hirtz\Config\tests\support\FunctionalTester;
use Hirtz\Skeleton\codeception\fixtures\UserFixtureTrait;
use Hirtz\Skeleton\codeception\functional\BaseCest;
use Hirtz\Skeleton\models\User;
use Hirtz\Skeleton\modules\admin\widgets\forms\LoginActiveForm;
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
