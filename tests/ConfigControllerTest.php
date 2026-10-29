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
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
        self::assertNotEmpty($this->getWebSession()->getFlash('success'));
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

    public function testASecretIsShownMaskedAndKeptWhenLeftEmpty(): void
    {
        $this->login();
        $this->saveSecret('sk-Grüße-東京-0123456789');

        $html = Yii::$app->runAction('admin/config/config/update');

        self::assertIsString($html);
        self::assertStringNotContainsString('0123456789', $html);
        self::assertStringNotContainsString('name="TestControllerConfig[cookieValidationKey]"', $html);
        self::assertStringContainsString('sk-G••••••••', $html);
        self::assertStringContainsString('state=replace', $html);
        self::assertStringContainsString('state=remove', $html);

        $this->post([self::FORM_NAME => ['siteName' => 'Renamed', 'cookieValidationKey' => '']]);

        self::assertSame('sk-Grüße-東京-0123456789', (require Yii::getAlias($this->configFile))['cookieValidationKey']);
    }

    public function testAnUnsavedSecretIsAPasswordField(): void
    {
        $this->login();

        $html = Yii::$app->runAction('admin/config/config/update');

        self::assertIsString($html);
        self::assertStringContainsString('name="TestControllerConfig[cookieValidationKey]"', $html);
        self::assertStringNotContainsString('state=remove', $html);
    }

    public function testTheSecretFieldSwapsBetweenItsStates(): void
    {
        $this->login();
        $this->saveSecret('sk-Ωmega-0123456789');

        $html = $this->postSensitive('cookieValidationKey', 'replace');

        self::assertStringContainsString('id="testcontrollerconfig-cookievalidationkey-row"', $html);
        self::assertStringContainsString('type="password"', $html);
        self::assertStringContainsString('name="TestControllerConfig[cookieValidationKey]"', $html);
        self::assertStringContainsString('placeholder="sk-Ω••••••••"', $html);
        self::assertStringContainsString('autofocus', $html);
        self::assertStringContainsString('Leave empty to keep the saved value.', $html);
        self::assertStringContainsString('state=saved', $html);

        $html = $this->postSensitive('cookieValidationKey', 'remove');

        self::assertStringContainsString('name="TestControllerConfig[removedAttributes][]" value="cookieValidationKey"', $html);
        self::assertStringNotContainsString('name="TestControllerConfig[cookieValidationKey]"', $html);
        self::assertStringContainsString('state=saved', $html);

        $html = $this->postSensitive('cookieValidationKey', 'saved');

        self::assertStringContainsString('sk-Ω••••••••', $html);
        self::assertStringNotContainsString('removedAttributes', $html);
    }

    public function testOnlyASecretFieldIsSwapped(): void
    {
        $this->login();
        $refused = 0;

        foreach ([['siteName', 'replace'], ['cookieValidationKey', 'reveal'], ['unknown', 'replace']] as [$attribute, $state]) {
            try {
                $this->postSensitive($attribute, $state);
            } catch (NotFoundHttpException) {
                $refused++;
            }
        }

        self::assertSame(3, $refused);
    }

    public function testASecretIsReplacedOrRemoved(): void
    {
        $this->login();
        $this->saveSecret('old-secret-0123456789');

        $this->post([self::FORM_NAME => ['cookieValidationKey' => 'new-secret-9876543210']]);
        self::assertSame('new-secret-9876543210', (require Yii::getAlias($this->configFile))['cookieValidationKey']);

        $this->post([self::FORM_NAME => ['cookieValidationKey' => '', 'removedAttributes' => ['cookieValidationKey', 'siteName']]]);

        $params = require Yii::getAlias($this->configFile);
        self::assertNull($params['cookieValidationKey'] ?? null);
        self::assertSame('Kept', $params['siteName'] ?? null);
    }

    public function testAValueTypedOverrulesTheRemoval(): void
    {
        $this->login();
        $this->saveSecret('old-secret-0123456789');

        $this->post([self::FORM_NAME => ['cookieValidationKey' => 'новый-秘密-🔑', 'removedAttributes' => ['cookieValidationKey']]]);

        self::assertSame('новый-秘密-🔑', (require Yii::getAlias($this->configFile))['cookieValidationKey']);
    }

    public function testAFailedSaveKeepsTheSecretFieldsState(): void
    {
        $this->login();
        $this->saveSecret('old-secret-0123456789');

        $html = $this->post([self::FORM_NAME => ['siteName' => str_repeat('a', 300), 'removedAttributes' => ['cookieValidationKey']]]);

        self::assertIsString($html);
        self::assertStringContainsString('name="TestControllerConfig[removedAttributes][]" value="cookieValidationKey"', $html);

        $html = $this->post([self::FORM_NAME => ['siteName' => str_repeat('a', 300), 'cookieValidationKey' => 'new-secret-9876543210']]);

        self::assertIsString($html);
        self::assertStringNotContainsString('new-secret', $html);
        self::assertStringContainsString('name="TestControllerConfig[cookieValidationKey]"', $html);
        self::assertSame('old-secret-0123456789', (require Yii::getAlias($this->configFile))['cookieValidationKey']);
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
        $this->getWebUser()->setIdentity($this->getUserFromFixture('admin'));

        $this->expectException(ForbiddenHttpException::class);
        Yii::$app->runAction('admin/config/config/update');
    }

    private function saveSecret(string $secret): void
    {
        $config = TestControllerConfig::create();
        $config->siteName = 'Kept';
        $config->cookieValidationKey = $secret;

        self::assertTrue($config->save(), print_r($config->getErrors(), true));
    }

    /**
     * @param array<string, mixed> $bodyParams
     */
    private function post(array $bodyParams): mixed
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = $this->getWebRequest();
        $request->setBodyParams([...$bodyParams, $request->csrfParam => $request->getCsrfToken()]);

        return Yii::$app->runAction('admin/config/config/update');
    }

    private function postSensitive(string $attribute, string $state): string
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = $this->getWebRequest();
        $request->setBodyParams([$request->csrfParam => $request->getCsrfToken()]);

        $html = Yii::$app->runAction('admin/config/config/sensitive', ['attribute' => $attribute, 'state' => $state]);
        self::assertIsString($html);

        return $html;
    }

    private function login(): User
    {
        $user = $this->getUserFromFixture('admin');
        $this->assignPermission($user->id, Config::AUTH_CONFIG);

        $this->getWebUser()->setIdentity($user);

        return $user;
    }
}

/**
 * @property string|null $siteName
 * @property string|null $cookieValidationKey
 */
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
            [
                ['cookieValidationKey'],
                'string',
            ],
        ];
    }
}
