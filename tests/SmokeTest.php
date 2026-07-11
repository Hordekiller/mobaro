<?php

use PHPUnit\Framework\TestCase;

class SmokeTest extends TestCase
{
    public function testPhpVersion(): void
    {
        $this->assertTrue(version_compare(PHP_VERSION, '8.1', '>='));
    }

    public function testBasePathConstant(): void
    {
        $this->assertNotEmpty(BASE_PATH);
        $this->assertIsString(BASE_PATH);
    }

    public function testConfigLoads(): void
    {
        $this->assertTrue(class_exists(Config::class));
    }
}
