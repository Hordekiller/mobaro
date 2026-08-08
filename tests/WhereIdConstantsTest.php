<?php

use App\Controllers\AdminController;
use App\Controllers\DashboardController;
use App\Controllers\ShopController;
use PHPUnit\Framework\TestCase;

class WhereIdConstantsTest extends TestCase
{
    public function testShopControllerWhereIdUsesNamedParameter(): void
    {
        $this->assertSame('id = :id', (new ReflectionClassConstant(ShopController::class, 'WHERE_ID'))->getValue());
    }

    public function testDashboardControllerWhereIdUsesNamedParameter(): void
    {
        $this->assertSame('id = :id', (new ReflectionClassConstant(DashboardController::class, 'WHERE_ID'))->getValue());
    }

    public function testAdminControllerWhereIdUsesNamedParameter(): void
    {
        $this->assertSame('id = :id', (new ReflectionClassConstant(AdminController::class, 'WHERE_ID'))->getValue());
    }
}
