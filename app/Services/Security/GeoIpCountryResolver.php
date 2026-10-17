<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use MaxMind\Db\Reader;
use MaxMind\Db\Reader\InvalidDatabaseException;

class GeoIpCountryResolver
{
    public function countryCode(string $ipAddress): ?string
    {
        if (! filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return null;
        }

        $databasePath = config('services.geoip.database_path');
        if (! is_string($databasePath) || ! is_file($databasePath) || ! is_readable($databasePath)) {
            Log::critical('Local GeoIP country database is unavailable; share rewards are disabled.');

            return null;
        }

        $reader = null;
        try {
            $reader = new Reader($databasePath);
            $record = $reader->get($ipAddress);
        } catch (InvalidArgumentException|InvalidDatabaseException $exception) {
            Log::error('Local GeoIP country lookup failed; share rewards are disabled.', [
                'exception' => $exception,
            ]);

            return null;
        } finally {
            $reader?->close();
        }

        $countryCode = $record['country']['iso_code'] ?? null;

        return is_string($countryCode) && preg_match('/^[A-Z]{2}$/', $countryCode)
            ? $countryCode
            : null;
    }
}
