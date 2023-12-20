<?php

namespace davidhirtz\yii2\config\tests\unit;

use Codeception\Test\Unit;
use davidhirtz\yii2\config\modules\admin\models\Config;
use davidhirtz\yii2\config\modules\admin\Module;
use Yii;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;
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

        $config = new TestConfig();

        $this->assertFalse($config->save());
        $this->assertEquals('test', Yii::$app->params['cookieValidationKey']);

        $config->cookieValidationKey = 'unit-test';

        $this->assertTrue($config->save());
        $this->assertEquals('unit-test', Yii::$app->params['cookieValidationKey']);
        $this->assertFileExists(Yii::getAlias($this->getConfigFile()));

        $config = new TestConfig();
        $this->assertEquals('unit-test', $config->cookieValidationKey);
    }

    public function testUpdateConfigWithEvent()
    {
        $config = new TestConfig();
        $config->cookieValidationKey = 'unit-test';
        $config->save();

        $config->cookieValidationKey = 'unit-test-2';

        $config->on(ActiveRecord::EVENT_AFTER_UPDATE, function (AfterSaveEvent $event) {
            $this->assertEquals(['cookieValidationKey' => 'unit-test'], $event->changedAttributes);
            $this->isEventTriggered = true;
        });

        $this->assertTrue($config->save());
        $this->assertTrue($this->isEventTriggered);
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
