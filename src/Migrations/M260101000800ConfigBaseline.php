<?php

declare(strict_types=1);

namespace Hirtz\Config\Migrations;

use Override;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */
class M260101000800ConfigBaseline extends Migration
{
    #[Override]
    public function safeUp(): void
    {
        $this->execute(
            <<<'SQL'
            INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `updated_at`, `created_at`) VALUES
              ('config', '2', '{\"category\":\"config\",\"key\":\"AUTH_CONFIG_DESCRIPTION\"}', NULL, NULL, '1789985581', '1789985581')
            SQL
        );

        $this->execute(
            <<<'SQL'
            INSERT INTO `auth_item_child` (`parent`, `child`) VALUES
              ('admin', 'config'),
              ('manager', 'config')
            SQL
        );
    }

    #[Override]
    public function safeDown(): bool
    {
        echo "    > a baseline cannot be reverted, restore a dump instead\n";
        return false;
    }
}
