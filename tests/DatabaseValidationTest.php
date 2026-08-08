<?php

use App\Database;
use PHPUnit\Framework\TestCase;

class DatabaseValidationTest extends TestCase
{
    private function getAllowedTablesProperty(): ReflectionProperty
    {
        $reflection = new ReflectionClass(Database::class);
        $property = $reflection->getProperty('allowedTables');
        return $property;
    }

    private function getValidateColumnsMethod(): ReflectionMethod
    {
        $reflection = new ReflectionClass(Database::class);
        $method = $reflection->getMethod('validateColumns');
        return $method;
    }

    public function testInsertRejectsInvalidTableName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table name');
        Database::insert('nonexistent_table', ['id' => 1]);
    }

    public function testUpdateRejectsInvalidTableName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table name');
        Database::update('nonexistent_table', ['id' => 1], 'id = 1');
    }

    public function testDeleteRejectsInvalidTableName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table name');
        Database::delete('nonexistent_table', 'id = 1');
    }

    public function testInsertRejectsInvalidColumnName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid column name');
        Database::insert('users', ['DROP TABLE users;' => 'value']);
    }

    public function testUpdateRejectsInvalidColumnName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid column name');
        Database::update('users', ['evil; --' => 'value'], 'id = 1');
    }

    public function testEscapeLikeEscapesPercentAndUnderscore(): void
    {
        $result = Database::escapeLike('100%_test');
        $this->assertSame('100\\%\\_test', $result);
    }

    public function testEscapeLikeLeavesOtherCharsUntouched(): void
    {
        $result = Database::escapeLike('hello world');
        $this->assertSame('hello world', $result);
    }

    public function testEscapeLikeEmptyString(): void
    {
        $this->assertSame('', Database::escapeLike(''));
    }

    public function testAllowedTablesAcceptValidNames(): void
    {
        $allowed = $this->getAllowedTablesProperty()->getValue();

        $this->assertContains('users', $allowed);
        $this->assertContains('services', $allowed);
        $this->assertContains('blog_posts', $allowed);
        $this->assertContains('coupons', $allowed);
        $this->assertContains('contact_messages', $allowed);
    }

    public function testAllowedTablesDoesNotContainWalletTopups(): void
    {
        $allowed = $this->getAllowedTablesProperty()->getValue();

        $this->assertNotContains('wallet_topups', $allowed);
    }

    public function testValidateColumnsRejectsSqlInjection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getValidateColumnsMethod()->invoke(null, ['id; DROP TABLE users']);
    }

    public function testValidateColumnsAcceptsValidNames(): void
    {
        $this->getValidateColumnsMethod()->invoke(null, ['id', 'user_name', '_private', 'col123']);
        $this->assertTrue(true);
    }
}
