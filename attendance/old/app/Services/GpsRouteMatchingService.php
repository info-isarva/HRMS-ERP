<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GpsRouteMatchingService
{
    /**
     * Build a road-aligned display route from raw GPS pings (free OpenRouteService API).
     *
     * @param  array<int, array{lat: float, lng: float, recorded_at?: string|null, accuracy?: float|null}>  $rawRoute
     * @return array{points: array<int, array{lat: float, lng: float}>, matched: bool, source: string}
     */
    public function buildDisplayRoute(int $employeeId, string $trackDate, array $rawRoute): array
    {
        $filtered = $this->filterOutliers($rawRoute);

        if (count($filtered) < 2) {
            return [
                'points' => $this->toDisplayPoints($filtered),
                'matched' => false,
                'source' => 'raw',
            ];
        }

        if (! config('gps.route_matching.enabled') || ! config('gps.route_matching.api_key')) {
            return [
                'points' => $this->toDisplayPoints($filtered),
                'matched' => false,
                'source' => 'raw_filtered',
            ];
        }

        $cacheKey = $this->cacheKey($employeeId, $trackDate, $filtered);

        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return $cached;
        }

        $matched = $this->matchViaOpenRouteService($filtered);

        $result = $matched ?? [
            'points' => $this->toDisplayPoints($filtered),
            'matched' => false,
            'source' => 'raw_filtered',
        ];

        Cache::put($cacheKey, $result, now()->addHours((int) config('gps.route_matching.cache_hours', 12)));

        return $result;
    }

    /**
     * @param  array<int, array{lat: float, lng: float, recorded_at?: string|null, accuracy?: float|null}>  $route
     * @return array<int, array{lat: float, lng: float, recorded_at?: string|null, accuracy?: float|null}>
     */
    public function filterOutliers(array $route): array
    {
        if ($route === []) {
            return [];
        }

        $maxAccuracy = (float) config('gps.route_matching.max_accuracy_meters', 80);
        $maxJumpMeters = (float) config('gps.route_matching.max_jump_meters', 400);
        $maxJumpSeconds = (int) config('gps.route_matching.max_jump_seconds', 30);

        $filtered = [];
        $previous = null;

        foreach ($route as $point) {
            $lat = (float) $point['lat'];
            $lng = (float) $point['lng'];

            if (isset($point['accuracy']) && $point['accuracy'] !== null && (float) $point['accuracy'] > $maxAccuracy) {
                continue;
            }

            if ($previous) {
                $distance = $this->haversineMeters($previous['lat'], $previous['lng'], $lat, $lng);
                $seconds = $this->secondsBetween($previous['recorded_at'] ?? null, $point['recorded_at'] ?? null);

                if ($seconds !== null && $seconds > 0 && $seconds <= $maxJumpSeconds && $distance > $maxJumpMeters) {
                    continue;
                }
            }

            $filtered[] = $point;
            $previous = $point;
        }

        return $filtered;
    }

    /**
     * @param  array<int, array{lat: float, lng: float}>  $route
     * @return array<int, array{lat: float, lng: float}>
     */
    private function downsampleWaypoints(array $route): array
    {
        $max = (int) config('gps.route_matching.max_waypoints', 40);

        if (count($route) <= $max) {
            return $route;
        }

        $lastIndex = count($route) - 1;
        $picked = [0];
        $slots = $max - 2;

        for ($i = 1; $i <= $slots; $i++) {
            $picked[] = (int) round($i * $lastIndex / ($slots + 1));
        }

        $picked[] = $lastIndex;
        $picked = array_values(array_unique($picked));
        sort($picked);

        return array_map(fn (int $index) => $route[$index], $picked);
    }

    /**
     * @param  array<int, array{lat: float, lng: float}>  $route
     * @return array{points: array<int, array{lat: float, lng: float}>, matched: bool, source: string}|null
     */
    private function matchViaOpenRouteService(array $route): ?array
    {
        $waypoints = $this->downsampleWaypoints($route);
        $coordinates = array_map(
            fn (array $point) => [(float) $point['lng'], (float) $point['lat']],
            $waypoints
        );

        $profile = (string) config('gps.route_matching.profile', 'driving-car');
        $baseUrl = rtrim((string) config('gps.route_matching.api_url'), '/');
        $url = "{$baseUrl}/v2/directions/{$profile}/geojson";

        try {
            $response = Http::timeout((int) config('gps.route_matching.timeout_seconds', 20))
                ->withHeaders([
                    'Authorization' => (string) config('gps.route_matching.api_key'),
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'coordinates' => $coordinates,
                    'radiuses' => array_fill(0, count($coordinates), -1),
                    'geometry_simplify' => true,
                ]);

            if (! $response->successful()) {
                Log::warning('OpenRouteService route matching failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $geometry = $response->json('features.0.geometry');
            if (! is_array($geometry) || ($geometry['type'] ?? '') !== 'LineString') {
                return null;
            }

            $points = [];
            foreach ($geometry['coordinates'] ?? [] as $coord) {
                if (! is_array($coord) || count($coord) < 2) {
                    continue;
                }

                $points[] = [
                    'lat' => (float) $coord[1],
                    'lng' => (float) $coord[0],
                ];
            }

            if (count($points) < 2) {
                return null;
            }

            return [
                'points' => $points,
                'matched' => true,
                'source' => 'openrouteservice',
            ];
        } catch (\Throwable $e) {
            Log::warning('OpenRouteService route matching exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<int, array{lat: float, lng: float}>  $route
     */
    private function cacheKey(int $employeeId, string $trackDate, array $route): string
    {
        $signature = md5(json_encode(array_map(
            fn (array $point) => [
                round((float) $point['lat'], 5),
                round((float) $point['lng'], 5),
                $point['recorded_at'] ?? null,
            ],
            $route
        )));

        return "gps_matched_route:{$employeeId}:{$trackDate}:{$signature}";
    }

    /**
     * @param  array<int, array{lat: float, lng: float, recorded_at?: string|null}>  $route
     * @return array<int, array{lat: float, lng: float}>
     */
    private function toDisplayPoints(array $route): array
    {
        return array_map(
            fn (array $point) => [
                'lat' => (float) $point['lat'],
                'lng' => (float) $point['lng'],
            ],
            $route
        );
    }

    private function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function secondsBetween(?string $from, ?string $to): ?int
    {
        if (! $from || ! $to) {
            return null;
        }

        $start = strtotime($from);
        $end = strtotime($to);

        if ($start === false || $end === false) {
            return null;
        }

        return abs($end - $start);
    }
}
