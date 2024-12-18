<?php

declare(strict_types=1);

namespace davidhirtz\yii2\config\tests\unit;

use Codeception\Test\Unit;
use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\config\tests\support\UnitTester;
use Yii;
use yii\db\AfterSaveEvent;
use yii\db\BaseActiveRecord;

class ConfigTest extends Unit
{
    protected UnitTester $tester;

    protected function _before(): void
    {
        $this->tester->deleteConfigFile();
        parent::_before();
    }

    protected function _after(): void
    {
        $this->tester->deleteConfigFile();
        parent::_after();
    }

    public function testCreateConfig()
    {
        $config = TestConfig::create();

        self::assertFalse($config->save());
        self::assertEquals('test', Yii::$app->params['cookieValidationKey']);

        $config->cookieValidationKey = 'unit-test';

        self::assertTrue($config->save());
        self::assertEquals('unit-test', Yii::$app->params['cookieValidationKey']);
        self::assertFileExists(Yii::getAlias($this->tester->getConfigFile()));

        $config = TestConfig::create();
        self::assertEquals('unit-test', $config->cookieValidationKey);
    }

    public function testUpdateConfig()
    {
        $config = TestConfig::create();
        $config->cookieValidationKey = 'unit-test';

        self::assertTrue($config->save());

        $config->cookieValidationKey = 'unit-test-2';

        $isTriggered = false;

        $config->on(BaseActiveRecord::EVENT_AFTER_UPDATE, function (AfterSaveEvent $event) use (&$isTriggered) {
            self::assertEquals(['cookieValidationKey' => 'unit-test'], $event->changedAttributes);
            $isTriggered = true;
        });

        self::assertTrue($config->save());
        self::assertTrue($isTriggered);

        self::assertEquals('unit-test-2', $config->cookieValidationKey);
        self::assertEquals('unit-test-2', Yii::$app->params['cookieValidationKey']);
    }

    public function testSaveI18nAttribute()
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

        $trail = $this->tester->loadLastTrail();

        self::assertEquals(TestConfig::class, $trail->model);
        self::assertEquals($config->getTrailModelName(), $trail->getModelName());
        self::assertEquals(['cookieValidationKey' => [null, 'trail-test']], $trail->data);

        $config->cookieValidationKey = 'trail-test-2';
        $config->save();

        $trail = $this->tester->loadLastTrail();

        self::assertEquals(TestConfig::class, $trail->model);
        self::assertEquals(['cookieValidationKey' => ['trail-test', 'trail-test-2']], $trail->data);
    }
}

/**
 * @property string $cookieValidationKey
 */
class TestConfig extends Config
{
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
    public array $i18nAttributes = ['cookieValidationKey'];
}
