<?php

use App\Controllers\BookingController;
use PHPUnit\Framework\TestCase;

final class BookingControllerTest extends TestCase
{
    /**
     * Regression test: PDO returns DECIMAL columns as strings (e.g. services.price).
     * calculateFinalPrice() must accept a string base price and return an int,
     * matching shop/course price normalization — never a TypeError.
     */
    public function testCalculateFinalPriceAcceptsStringBasePrice(): void
    {
        $controller = new BookingController();
        $method = new ReflectionMethod(BookingController::class, 'calculateFinalPrice');

        $result = $method->invoke($controller, 1, 0, '380000');

        $this->assertIsInt($result['price']);
        $this->assertSame(380000, $result['price']);
        $this->assertSame(1.0, $result['modifier']);
    }

    public function testCalculateFinalPriceAcceptsIntBasePrice(): void
    {
        $controller = new BookingController();
        $method = new ReflectionMethod(BookingController::class, 'calculateFinalPrice');

        $result = $method->invoke($controller, 1, 0, 380000);

        $this->assertIsInt($result['price']);
        $this->assertSame(380000, $result['price']);
    }

    public function testCalculateFinalPriceNormalizesPersianDigits(): void
    {
        $controller = new BookingController();
        $method = new ReflectionMethod(BookingController::class, 'calculateFinalPrice');

        $result = $method->invoke($controller, 1, 0, '۳۸۰,۰۰۰');

        $this->assertIsInt($result['price']);
        $this->assertSame(380000, $result['price']);
    }
}
