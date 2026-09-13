<?php

declare(strict_types=1);

namespace Hirtz\Config\Tests;

use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Skeleton\Models\Trail;
use Hirtz\Skeleton\Test\TestCase;
use Override;
use Yii;
use yii\db\AfterSaveEvent;
use yii\db\BaseActiveRecord;
use yii\helpers\FileHelper;

class ConfigTest extends TestCase
{
    private string $configFile = '@runtime/config/params.php';

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $configFile = Yii::getAlias($this->configFile);

        Config::getModule()->configFile = $configFile;
        FileHelper::createDirectory(dirname($configFile));
    }

    #[\Override]
    protected function tearDown(): void
    {
        FileHelper::removeDirectory(dirname(Config::getModule()->configFile));
        parent::tearDown();
    }

    public function testCreateConfig(): void
    {
        $config = TestConfig::create();

        self::assertFalse($config->save());
        self::assertEquals('test', Yii::$app->params['cookieValidationKey']);

        $config->cookieValidationKey = 'unit-test';

        self::assertTrue($config->save());
        self::assertEquals('unit-test', Yii::$app->params['cookieValidationKey']);
        self::assertFileExists(Yii::getAlias($this->configFile));

        $config = TestConfig::create();
        self::assertEquals('unit-test', $config->cookieValidationKey);
    }

    public function testUpdateConfig(): void
    {
        $config = TestConfig::create();
        $config->cookieValidationKey = 'unit-test';

        self::assertTrue($config->save());

        $config->cookieValidationKey = 'unit-test-2';

        $isTriggered = false;

        $config->on(BaseActiveRecord::EVENT_AFTER_UPDATE, function (AfterSaveEvent $event) use (&$isTriggered): void {
            self::assertEquals(['cookieValidationKey' => 'unit-test'], $event->changedAttributes);
            $isTriggered = true;
        });

        self::assertTrue($config->save());
        self::assertTrue($isTriggered);

        self::assertEquals('unit-test-2', $config->cookieValidationKey);
        self::assertEquals('unit-test-2', Yii::$app->params['cookieValidationKey']);
    }

    public function testSaveI18nAttribute(): void
    {
        Yii::$app->getI18n()->languages = ['de', 'en-US'];

        $config = TestConfigI18n::create();
        $config->cookieValidationKey = 'unit-test';

        self::assertFalse($config->save());
        self::assertArrayHasKey('cookieValidationKey_de', $config->getErrors());

        $config->cookieValidationKey_de = 'unit-test-de';

        self::assertTrue($config->save());

        self::assertEquals('unit-test-de', $config->cookieValidationKey_de);
        self::assertEquals('unit-test-de', Yii::$app->params['cookieValidationKey_de']);

    }

    public function testTrailIntegration(): void
    {
        $config = TestConfig::create();
        $config->cookieValidationKey = 'trail-test';
        $config->save();

        $trail = $this->loadLastTrail();

        self::assertEquals(TestConfig::class, $trail->model_class);
        self::assertEquals($config->getAdminName(), $trail->getModelName());
        self::assertEquals(['cookieValidationKey' => [null, 'trail-test']], $trail->data);

        $config->cookieValidationKey = 'trail-test-2';
        $config->save();

        $trail = $this->loadLastTrail();

        self::assertEquals(TestConfig::class, $trail->model_class);
        self::assertEquals(['cookieValidationKey' => ['trail-test', 'trail-test-2']], $trail->data);
    }

    private function loadLastTrail(): ?Trail
    {
        return Trail::find()
            ->orderBy(['id' => SORT_DESC])
            ->one();
    }
}

/**
 * @property string $cookieValidationKey
 */
class TestConfig extends Config
{
    #[Override]
    public function rules(): array
    {
        return $this->getI18nRules([
            [
                ['cookieValidationKey'],
                'required',
            ],
        ]);
    }
}

/**
 * @property string $cookieValidationKey_de
 */
class TestConfigI18n extends TestConfig
{
    /** @var string[] */
    public array $i18nAttributes = ['cookieValidationKey'];
}
