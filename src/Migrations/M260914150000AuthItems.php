<?php

declare(strict_types=1);

namespace Hirtz\Config\Migrations;

use Hirtz\Config\Modules\Admin\Models\Config;
use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\I18n\Message;
use Hirtz\Skeleton\Models\User;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */
class M260914150000AuthItems extends Migration
{
    use MigrationTrait;

    private const array LEGACY_CONFIG = ['configUpdate'];

    public function safeUp(): void
    {
        $this->addPermission(Config::AUTH_CONFIG, $this->getConfigDescription(), User::AUTH_ROLE_ADMIN);
        $this->replaceAuthItems(self::LEGACY_CONFIG, Config::AUTH_CONFIG);
    }

    public function safeDown(): void
    {
        $this->restoreAuthItems(self::LEGACY_CONFIG, Config::AUTH_CONFIG, $this->getConfigDescription());
    }

    private function getConfigDescription(): Message
    {
        return Message::make('config', 'AUTH_CONFIG_DESCRIPTION');
    }
}
