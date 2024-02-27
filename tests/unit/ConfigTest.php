<?php

namespace davidhirtz\yii2\config\tests\unit;

use Codeception\Test\Unit;
use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\config\modules\admin\Module;
use Yii;
use yii\db\AfterSaveEvent;
use yii\db\BaseActiveRecord;
use yii\helpers\FileHelper;

class ConfigTest extends Unit
{
    private ?string $_configFile = null;
    private bool $isEventTriggered = false;

    protected function _before(): void
    {
        $this->deleteConfigFile();
        parent::_before();
    }

    protected function _after(): void
    {
        $this->deleteConfigFile();
        parent::_after();
    }

    public function testCreateConfig()
    {
        codecept_debug(Yii::$app->params);

        $config = TestConfig::create();

        self::assertFalse($config->save());
        self::assertEquals('test', Yii::$app->params['cookieValidationKey']);

        $config->cookieValidationKey = 'unit-test';

        self::assertTrue($config->save());
        self::assertEquals('unit-test', Yii::$app->params['cookieValidationKey']);
        self::assertFileExists(Yii::getAlias($this->getConfigFile()));

        $config = TestConfig::create();
        self::assertEquals('unit-test', $config->cookieValidationKey);
    }

    public function testUpdateConfigWithEvent()
    {
        $config = TestConfig::create();
        $config->cookieValidationKey = 'unit-test';
        $config->save();

        $config->cookieValidationKey = 'unit-test-2';

        $config->on(BaseActiveRecord::EVENT_AFTER_UPDATE, function (AfterSaveEvent $event) {
            self::assertEquals(['cookieValidationKey' => 'unit-test'], $event->changedAttributes);
            $this->isEventTriggered = true;
        });

        self::assertTrue($config->save());
        self::assertTrue($this->isEventTriggered);
    }

    protected function getConfigFile(): string
    {
        if ($this->_configFile === null) {
            /** @var Module $module */
            $module = Yii::$app->getModule('admin')->getModule('config');
            $this->_configFile = $module->configFile;
        }

        return $this->_configFile;
    }

    protected function deleteConfigFile(): void
    {
        $file = Yii::getAlias($this->getConfigFile());
        FileHelper::removeDirectory(dirname($file));
    }
}

class TestConfig extends Config
{
    public ?string $cookieValidationKey = null;

    public function rules(): array
    {
        return [
            [
                ['cookieValidationKey'],
                'required',
            ],
        ];
    }
}
