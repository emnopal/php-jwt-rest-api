<?php

namespace BadHabit\LoginManagement\Config;

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{

    public function testGetConnection()
    {
        $connection = Database::getConnection();
        self::assertNotNull($connection);
    }

    // Get connection is singleton, so if we call it again, it should return the same connection
    public function testGetConnectionSingleton()
    {
        $connection1 = Database::getConnection();
        $connection2 = Database::getConnection();
        self::assertSame($connection1, $connection2);
    }
}
