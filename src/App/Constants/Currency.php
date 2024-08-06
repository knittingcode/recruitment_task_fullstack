<?php namespace App\Constants;

/**
 * Class Currency
 *
 * This class contains constants for supported currencies and their full names.
 */
class Currency {

    // Currency codes
    const EUR = 'EUR';
    const USD = 'USD';
    const CZK = 'CZK';
    const IDR = 'IDR';
    const BRL = 'BRL';

    // Currency names
    private static $names = [
        self::EUR => 'Euro',
        self::USD => 'US Dollar',
        self::CZK => 'Czech Koruna',
        self::IDR => 'Indonesian Rupiah',
        self::BRL => 'Brazilian Real',
    ];

    /**
     * Get the full name of a currency based on its code.
     *
     * @param string $currency The currency code (e.g., 'EUR', 'USD').
     * @return string The full name of the currency, or 'Unknown' if the currency is not supported.
     */
    public static function getName(string $currency): string {
        return self::$names[$currency] ?? 'Unknown';
    }

    /**
     * Get all supported currency codes.
     *
     * @return array An array of supported currency codes.
     */
    public static function getAll(): array {
        return array_keys(self::$names);
    }

}