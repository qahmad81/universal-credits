<?php

namespace App\Services;

use InvalidArgumentException;

class UCMask
{
    public const UC_PER_DOLLAR = 10000;
    public const DB_MULTIPLIER = 100;

    /**
     * Convert application value to DB integer (x100).
     * 1 USD = 10,000 UC (app) = 1,000,000 (DB)
     */
    public static function toDb(float $appValue): int
    {
        self::validate($appValue);

        $dbValue = $appValue * self::DB_MULTIPLIER;

        if ($dbValue > PHP_INT_MAX) {
            throw new InvalidArgumentException('Value exceeds maximum allowed integer size in DB.');
        }

        return (int) round($dbValue);
    }

    /**
     * Convert DB integer back to application float value.
     */
    public static function fromDb(int $dbValue): float
    {
        return $dbValue / self::DB_MULTIPLIER;
    }

    /**
     * Convert application value to display integer (rounded).
     */
    public static function toDisplay(float $appValue): int
    {
        self::validate($appValue);

        return (int) round($appValue);
    }

    /**
     * Convert dollars to application UC float.
     */
    public static function dollarsToUc(float $dollars): float
    {
        self::validate($dollars);

        return $dollars * self::UC_PER_DOLLAR;
    }

    /**
     * Convert application UC float to dollars.
     */
    public static function ucToDollars(float $uc): float
    {
        self::validate($uc);

        return $uc / self::UC_PER_DOLLAR;
    }

    /**
     * Common validation for UC values.
     */
    private static function validate(float $value): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException('UC values cannot be negative.');
        }
    }
}
