<?php

namespace Tests\Unit;

use App\Services\UCMask;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UCMaskTest extends TestCase
{
    public function test_to_db_conversion(): void
    {
        $this->assertEquals(100, UCMask::toDb(1.0));
        $this->assertEquals(12880, UCMask::toDb(128.80));
        $this->assertEquals(0, UCMask::toDb(0.0));
        $this->assertEquals(1, UCMask::toDb(0.01));
    }

    public function test_from_db_conversion(): void
    {
        $this->assertEquals(1.0, UCMask::fromDb(100));
        $this->assertEquals(128.80, UCMask::fromDb(12880));
        $this->assertEquals(0.0, UCMask::fromDb(0));
    }

    public function test_to_display_rounding(): void
    {
        $this->assertEquals(129, UCMask::toDisplay(128.80));
        $this->assertEquals(128, UCMask::toDisplay(128.49));
        $this->assertEquals(129, UCMask::toDisplay(128.50));
        $this->assertEquals(0, UCMask::toDisplay(0.0));
    }

    public function test_dollar_conversions(): void
    {
        $this->assertEquals(10000.00, UCMask::dollarsToUc(1.0));
        $this->assertEquals(1.0, UCMask::ucToDollars(10000.00));
        
        // 1 dollar = 10k UC app = 1M DB
        $appUc = UCMask::dollarsToUc(1.0);
        $this->assertEquals(1000000, UCMask::toDb($appUc));
    }

    public function test_negative_values_throw_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        UCMask::toDb(-1.0);
    }

    public function test_negative_dollars_throw_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        UCMask::dollarsToUc(-1.0);
    }

    public function test_overflow_protection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        // PHP_INT_MAX is huge, multiplying it by 100 will definitely overflow
        UCMask::toDb((float)PHP_INT_MAX);
    }

    public function test_round_trip_integrity(): void
    {
        $original = 1234.56;
        $db = UCMask::toDb($original);
        $back = UCMask::fromDb($db);
        $this->assertEquals($original, $back);
    }
}
