<?php

declare(strict_types=1);

namespace Hirtz\Config\Tests;

use Hirtz\Skeleton\Test\TestCase;
use Hirtz\Skeleton\Test\Traits\MessageKeysTrait;

class MessageKeysTest extends TestCase
{
    use MessageKeysTrait;

    public function testTheConfigKeysFollowTheScheme(): void
    {
        self::assertMessageKeys(dirname(__DIR__) . '/messages', 'config');
    }
}
