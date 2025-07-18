<?php
namespace App\Lib;

class RegionPolicy {
    const EU = 'EU_GDPR';
    const US = 'US_COPPA';
    const GENERAL = 'GENERAL';

    public static function selfConsentMinAge(string $region = self::US): int {
        switch ($region) {
            case self::EU:
                return 16;
            case self::US:
                return 13;
            default:
                return 13;
        }
    }

    public static function adultMinAge(string $region = self::US): int {
        switch ($region) {
            case self::EU:
                return 16;
            case self::US:
                return 18;
            default:
                return 18;
        }
    }

    /**
     * Get the region policy based on country code
     */
    public static function getRegionFromCountry(string $countryCode): string {
        $euCountries = [
            'DE', 'FR', 'ES', 'IT', 'NL', 'SE', 'PL', 'BE', 'AT', 'IE', 'FI', 'PT', 'CZ',
            'HU', 'GR', 'RO', 'DK', 'BG', 'HR', 'EE', 'LT', 'LV', 'LU', 'MT', 'SI', 'SK', 'CY'
        ];

        if (in_array($countryCode, $euCountries)) {
            return self::EU;
        } elseif ($countryCode === 'US') {
            return self::US;
        }
        return self::GENERAL;
    }

    public static function requiresParentalConsent(?int $age, string $region = self::US): bool {
        return is_null($age) || $age < self::selfConsentMinAge($region);
    }
}
