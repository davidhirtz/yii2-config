<?php

declare(strict_types=1);

namespace Hirtz\Config\Tests;

use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Skeleton\Behaviors\TrailBehavior;
use Hirtz\Skeleton\Filters\PageCache;
use Hirtz\Skeleton\Models\Trail;
use Hirtz\Skeleton\Test\TestCase;
use Override;
use Yii;
use yii\caching\TagDependency;
use yii\db\AfterSaveEvent;
use yii\db\BaseActiveRecord;
use yii\helpers\FileHelper;

class ConfigTest extends TestCase
{
    private const string CONFIG_FILE = '@runtime/config/params.php';

    private string $configFile;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->configFile = Yii::getAlias(self::CONFIG_FILE);

        Config::getModule()->configFile = $this->configFile;
        FileHelper::createDirectory(dirname($this->configFile));
    }

    #[\Override]
    protected function tearDown(): void
    {
        if (isset($this->configFile)) {
            FileHelper::removeDirectory(dirname($this->configFile));
        }

        parent::tearDown();
    }

    /**
     * The module belongs to the application that built it, so it is looked up rather than memoised — a static one
     * outlived the application and handed the next request a module of a dead one.
     */
    public function testTheModuleIsTheOneOfTheCurrentApplication(): void
    {
        $module = Config::getModule();

        $this->reloadApplication();

        $reloaded = Config::getModule();

        self::assertNotSame($module, $reloaded);
    }

    public function testCreateConfig(): void
    {
        $config = TestConfig::create();

        self::assertFalse($config->save());
        self::assertEquals('test', Yii::$app->params['cookieValidationKey']);

        $config->cookieValidationKey = 'unit-test';

        self::assertTrue($config->save(), print_r($config->getErrors(), true));
        self::assertEquals('unit-test', Yii::$app->params['cookieValidationKey']);
        self::assertFileExists($this->configFile);

        $config = TestConfig::create();
        self::assertEquals('unit-test', $config->cookieValidationKey);
    }

    public function testSavingInvalidatesThePageCache(): void
    {
        $cache = Yii::$app->getCache();
        $cache->set('page', 'cached', 0, new TagDependency(['tags' => [PageCache::TAG_DEPENDENCY_KEY]]));

        $config = TestConfig::create();
        $config->cookieValidationKey = 'unit-test';

        self::assertTrue($config->save(), print_r($config->getErrors(), true));
        self::assertFalse($cache->get('page'));
    }

    public function testUpdateConfig(): void
    {
        $config = TestConfig::create();
        $config->cookieValidationKey = 'unit-test';

        self::assertTrue($config->save(), print_r($config->getErrors(), true));

        $config->cookieValidationKey = 'unit-test-2';

        $isTriggered = false;

        $config->on(BaseActiveRecord::EVENT_AFTER_UPDATE, function (AfterSaveEvent $event) use (&$isTriggered): void {
            self::assertEquals(['cookieValidationKey' => 'unit-test'], $event->changedAttributes);
            $isTriggered = true;
        });

        self::assertTrue($config->save(), print_r($config->getErrors(), true));
        self::assertTrue($isTriggered);

        self::assertEquals('unit-test-2', $config->cookieValidationKey);
        self::assertEquals('unit-test-2', Yii::$app->params['cookieValidationKey']);
    }

    /**
     * `params.php` holds scalars, but a hand-edited array under a key the form does not own must not stop a save.
     */
    public function testAnArrayElsewhereInTheFileDoesNotStopASave(): void
    {
        file_put_contents($this->configFile, "<?php\n\nreturn ['cookieValidationKey' => 'test', 'hosts' => ['a', 'b']];\n");

        $config = TestConfig::create();
        $config->cookieValidationKey = 'unit-test';

        self::assertTrue($config->save(), print_r($config->getErrors(), true));

        $params = require $this->configFile;
        self::assertSame(['a', 'b'], $params['hosts']);
        self::assertSame('unit-test', $params['cookieValidationKey']);
    }

    public function testTypedParamsAreWrittenTyped(): void
    {
        file_put_contents($this->configFile, "<?php\n\nreturn ['cookieValidationKey' => 'k', 'enableFoo' => false, 'pageSize' => 20, 'ratio' => 1];\n");
        $config = TestConfig::create();

        $config->load(['TestConfig' => ['enableFoo' => '0', 'pageSize' => '20', 'ratio' => '1']]);
        self::assertFalse($config->isChangedAttribute('enableFoo'));
        self::assertFalse($config->isChangedAttribute('pageSize'));
        self::assertFalse($config->isChangedAttribute('ratio'));

        $config->load(['TestConfig' => ['enableFoo' => '1', 'pageSize' => '25', 'ratio' => '1.5', 'claim' => '7']]);
        self::assertTrue($config->save(), print_r($config->getErrors(), true));

        $params = require $this->configFile;
        self::assertTrue($params['enableFoo']);
        self::assertSame(25, $params['pageSize']);
        self::assertSame(1.5, $params['ratio']);
        self::assertSame('7', $params['claim']);
        self::assertTrue(Yii::$app->params['enableFoo']);
    }

    public function testAnUntouchedFalseIsNoTrailEntry(): void
    {
        file_put_contents($this->configFile, "<?php\n\nreturn ['cookieValidationKey' => 'k', 'enableFoo' => false];\n");
        $config = TestConfig::create();
        $lastId = $this->loadLastTrail()?->id;

        $config->load(['TestConfig' => ['enableFoo' => '0']]);

        self::assertFalse($config->save());
        self::assertSame($lastId, $this->loadLastTrail()?->id);
    }

    public function testSaveI18nAttribute(): void
    {
        Yii::$app->getI18n()->languages = ['de', 'en-US'];

        $config = TestConfigI18n::create();
        $config->cookieValidationKey = 'unit-test';

        self::assertFalse($config->save());
        self::assertArrayHasKey('cookieValidationKey_de', $config->getErrors());

        $config->cookieValidationKey_de = 'unit-test-de';

        self::assertTrue($config->save(), print_r($config->getErrors(), true));

        self::assertEquals('unit-test-de', $config->cookieValidationKey_de);
        self::assertEquals('unit-test-de', Yii::$app->params['cookieValidationKey_de']);

    }

    public function testTrailIntegration(): void
    {
        $config = TestConfig::create();
        $config->cookieValidationKey = 'secret';
        $config->claim = 'trail-test';
        $config->save();

        $trail = $this->loadLastTrail();

        self::assertEquals(TestConfig::class, $trail->model_class);
        self::assertEquals($config->getAdminName(), $trail->getModelName());
        self::assertEquals([
            'claim' => [null, 'trail-test'],
            'cookieValidationKey' => [null, TrailBehavior::MASKED_VALUE],
        ], $trail->data);

        $config->claim = 'trail-test-2';
        $config->save();

        $trail = $this->loadLastTrail();

        self::assertEquals(TestConfig::class, $trail->model_class);
        self::assertEquals(['claim' => ['trail-test', 'trail-test-2']], $trail->data);
    }

    public function testASecretParamIsMaskedInTheTrail(): void
    {
        $config = TestConfig::create();
        $config->cookieValidationKey = 'dummy-key-old';
        self::assertTrue($config->save(), print_r($config->getErrors(), true));

        $config->cookieValidationKey = 'dummy-key-new';
        self::assertTrue($config->save(), print_r($config->getErrors(), true));

        $data = $this->loadLastTrail()?->data;
        self::assertSame(['cookieValidationKey' => [TrailBehavior::MASKED_VALUE, TrailBehavior::MASKED_VALUE]], $data);
    }

    /**
     * The trail links the settings record to this route, and no test renders that link.
     */
    public function testTheAdminRouteResolvesToAController(): void
    {
        $route = TestConfig::create()->getAdminRoute();

        self::assertIsArray($route);
        self::assertNotFalse(Yii::$app->createController(ltrim($route[0], '/')));
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
 * @property string|null $claim
 * @property bool|null $enableFoo
 * @property int|null $pageSize
 * @property float|null $ratio
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
            [
                ['claim'],
                'string',
            ],
            [
                ['enableFoo'],
                'boolean',
            ],
            [
                ['pageSize'],
                'integer',
            ],
            [
                ['ratio'],
                'number',
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
    /**
     * @var list<string>
     */
    public array $i18nAttributes = ['cookieValidationKey'];
}
