<?php

declare(strict_types=1);

namespace Hirtz\Config\Tests;

use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Skeleton\Models\Trail;
use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Test\TestCase;
use Hirtz\Skeleton\Test\Traits\UserFixtureTrait;
use Override;
use Yii;
use yii\helpers\FileHelper;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * The config page writes a PHP file the application reads its params from, so a saved value has to survive the
 * round trip and leave a trail behind.
 */
class ConfigControllerTest extends TestCase
{
    use UserFixtureTrait;

    // The form is named after the model a project extends `Config` with, not after `Config` itself.
    private const string FORM_NAME = 'TestControllerConfig';

    private string $configFile = '@runtime/config/params.php';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $configFile = Yii::getAlias($this->configFile);

        Config::getModule()->configFile = $configFile;
        Yii::$container->set(Config::class, TestControllerConfig::class);

        FileHelper::createDirectory(dirname($configFile));
    }

    #[Override]
    protected function tearDown(): void
    {
        FileHelper::removeDirectory(dirname(Config::getModule()->configFile));
        parent::tearDown();
    }

    public function testTheFormRendersTheActiveAttributes(): void
    {
        $this->login();

        $html = Yii::$app->runAction('admin/config/config/update');

        self::assertIsString($html);
        self::assertStringContainsString('name="TestControllerConfig[siteName]"', $html);
    }

    public function testTheConfigIsWrittenAndReadBack(): void
    {
        $this->login();

        $response = $this->post([self::FORM_NAME => ['siteName' => 'A new name']]);

        self::assertInstanceOf(Response::class, $response);
        self::assertSame('A new name', Yii::$app->params['siteName']);

        self::assertSame('A new name', (require Yii::getAlias($this->configFile))['siteName']);
        self::assertNotEmpty(Yii::$app->getSession()->getFlash('success'));
    }

    public function testSavingLeavesATrail(): void
    {
        $this->login();
        $this->post([self::FORM_NAME => ['siteName' => 'A new name']]);

        $trail = Trail::find()
            ->orderBy(['id' => SORT_DESC])
            ->one();

        self::assertNotNull($trail);
        self::assertSame(TestControllerConfig::class, $trail->model_class);
    }

    public function testAnInvalidValueIsNotWritten(): void
    {
        $this->login();

        $html = $this->post([self::FORM_NAME => ['siteName' => str_repeat('a', 300)]]);

        self::assertIsString($html);
        self::assertFileDoesNotExist(Yii::getAlias($this->configFile));
    }

    public function testTheConfigPageIsForbiddenWithoutThePermission(): void
    {
        Yii::$app->getUser()->setIdentity($this->getUserFromFixture('admin'));

        $this->expectException(ForbiddenHttpException::class);
        Yii::$app->runAction('admin/config/config/update');
    }

    private function post(array $bodyParams): mixed
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = Yii::$app->getRequest();
        $request->setBodyParams([...$bodyParams, $request->csrfParam => $request->getCsrfToken()]);

        return Yii::$app->runAction('admin/config/config/update');
    }

    private function login(): User
    {
        $user = $this->getUserFromFixture('admin');
        $this->assignPermission($user->id, Config::AUTH_CONFIG);

        Yii::$app->getUser()->setIdentity($user);

        return $user;
    }
}

class TestControllerConfig extends Config
{
    #[Override]
    public function rules(): array
    {
        return [
            [
                ['siteName'],
                'string',
                'max' => 255,
            ],
        ];
    }
}
