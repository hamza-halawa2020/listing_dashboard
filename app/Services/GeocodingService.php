<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class GeocodingService
{
    private const BASE_URL = 'https://nominatim.openstreetmap.org/search';
    private const CACHE_KEY_PREFIX = 'listing_geocode:';

    /**
     * @return array{lat: float, lng: float}|null
     */
    public function geocodeFromCandidates(array $candidates): ?array
    {
        foreach ($candidates as $candidate) {
            $query = $this->buildQuery($candidate);

            if ($query === '') {
                continue;
            }

            $result = $this->requestCoordinates($query);

            if ($result) {
                return $result;
            }
        }

        return null;
    }

    private function buildQuery(string $value): string
    {
        return implode(', ', array_unique(array_filter([$value, 'Egypt'], static fn (string $part): bool => filled($part))));
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    private function requestCoordinates(string $query): ?array
    {
        $cacheKey = self::CACHE_KEY_PREFIX . md5($query);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($query) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => config('app.name', 'listing-importer') . ' (listing-dashboard)',
                    'Accept-Language' => 'ar,en',
                ])
                    ->timeout(10)
                    ->get(self::BASE_URL, [
                        'q' => $query,
                        'format' => 'json',
                        'limit' => 1,
                        'addressdetails' => 0,
                    ]);
            } catch (Throwable $exception) {
                return null;
            }

            if (! $response->successful()) {
                return null;
            }

            $payload = $response->json();

            if (! is_array($payload) || $payload === [] || ! isset($payload[0]['lat'], $payload[0]['lon'])) {
                return null;
            }

            return [
                'lat' => (float) $payload[0]['lat'],
                'lng' => (float) $payload[0]['lon'],
            ];
        });
    }
}
