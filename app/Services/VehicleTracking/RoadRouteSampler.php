<?php

namespace App\Services\VehicleTracking;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Builds road-aligned coordinate sequences using OSRM (driving profile).
 * Falls back to straight-line interpolation if the router is unavailable.
 */
class RoadRouteSampler
{
    /**
     * Named routes in Egypt: each entry is ordered [lat, lng] waypoints on real corridors.
     *
     * @var array<string, list<array{0: float, 1: float}>>
     */
    public const EGYPT_PRESETS = [
        'cairo_tahrir_heliopolis' => [
            [30.044419, 31.235746],
            [30.073358, 31.277842],
            [30.108947, 31.365812],
        ],
        'cairo_nasr_ring' => [
            [30.062600, 31.345600],
            [30.051100, 31.400200],
            [30.028900, 31.382400],
            [30.045500, 31.328900],
        ],
        'giza_pyramids_october' => [
            [29.979234, 31.134203],
            [29.965000, 31.050000],
            [29.969000, 30.939800],
        ],
        'alex_smouha_miami' => [
            [31.215000, 29.942000],
            [31.200000, 29.915000],
            [31.182000, 29.885000],
        ],
        'cairo_maadi_autostrade' => [
            [29.960200, 31.250400],
            [29.975500, 31.285000],
            [30.012000, 31.310000],
        ],
        'helwan_corridor' => [
            [29.841000, 31.334000],
            [29.880000, 31.320000],
            [29.920000, 31.300000],
        ],
        'zagazig_approach' => [
            [30.587200, 31.502200],
            [30.552000, 31.455000],
            [30.492000, 31.380000],
        ],
    ];

    /**
     * @param  list<array{0: float, 1: float}>  $waypointsLatLng
     * @return list<array{0: float, 1: float}>
     */
    public function routeAlongRoads(array $waypointsLatLng, int $targetSamples = 45): array
    {
        if (count($waypointsLatLng) < 2) {
            return $waypointsLatLng;
        }

        $coordsLngLat = $this->fetchOsrmGeometry($waypointsLatLng);
        if (count($coordsLngLat) < 2) {
            return $this->straightLineSample($waypointsLatLng, $targetSamples);
        }

        return $this->resampleAlongPolyline($coordsLngLat, $targetSamples);
    }

    /**
     * @param  list<array{0: float, 1: float}>  $waypointsLatLng
     * @return list<array{0: float, 1: float}>  [lat, lng]
     */
    private function fetchOsrmGeometry(array $waypointsLatLng): array
    {
        $base = config('vehicle_tracking.osrm_base_url', 'https://router.project-osrm.org');
        $path = collect($waypointsLatLng)
            ->map(fn (array $p) => sprintf('%F,%F', $p[1], $p[0]))
            ->implode(';');

        $url = $base . '/route/v1/driving/' . $path . '?overview=full&geometries=geojson&continue_straight=false';

        try {
            $response = Http::timeout(25)->acceptJson()->get($url);
            if (!$response->successful()) {
                Log::warning('RoadRouteSampler: OSRM non-success', ['status' => $response->status()]);

                return [];
            }
            $routes = $response->json('routes');
            if (!is_array($routes) || !isset($routes[0]['geometry']['coordinates'])) {
                return [];
            }
            $coordinates = $routes[0]['geometry']['coordinates'];
            if (!is_array($coordinates)) {
                return [];
            }

            $out = [];
            foreach ($coordinates as $pair) {
                if (!is_array($pair) || count($pair) < 2) {
                    continue;
                }
                $lng = (float) $pair[0];
                $lat = (float) $pair[1];
                $out[] = [$lat, $lng];
            }

            return $out;
        } catch (\Throwable $e) {
            Log::warning('RoadRouteSampler: OSRM request failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * @param  list<array{0: float, 1: float}>  $coordsLatLng
     * @return list<array{0: float, 1: float}>
     */
    private function resampleAlongPolyline(array $coordsLatLng, int $targetSamples): array
    {
        $n = count($coordsLatLng);
        if ($n < 2) {
            return $coordsLatLng;
        }

        $segLens = [];
        $total = 0.0;
        for ($i = 1; $i < $n; $i++) {
            $d = $this->segmentMeters($coordsLatLng[$i - 1], $coordsLatLng[$i]);
            $segLens[] = $d;
            $total += $d;
        }

        if ($total < 1.0) {
            return $coordsLatLng;
        }

        $samples = max(8, min(120, $targetSamples));
        $out = [];
        for ($s = 0; $s < $samples; $s++) {
            $distAlong = ($total * $s) / ($samples - 1);
            $out[] = $this->pointAtDistanceAlong($coordsLatLng, $segLens, $distAlong);
        }

        return $out;
    }

    /**
     * @param  list<array{0: float, 1: float}>  $waypointsLatLng
     * @return list<array{0: float, 1: float}>
     */
    private function straightLineSample(array $waypointsLatLng, int $targetSamples): array
    {
        $coords = [];
        $legs = count($waypointsLatLng) - 1;
        $perLeg = max(2, (int) ceil($targetSamples / $legs));

        for ($i = 0; $i < $legs; $i++) {
            $a = $waypointsLatLng[$i];
            $b = $waypointsLatLng[$i + 1];
            for ($t = 0; $t < $perLeg; $t++) {
                $u = $perLeg > 1 ? $t / ($perLeg - 1) : 0;
                $coords[] = [
                    $a[0] + ($b[0] - $a[0]) * $u,
                    $a[1] + ($b[1] - $a[1]) * $u,
                ];
            }
        }

        return $coords;
    }

    /**
     * @param  list<array{0: float, 1: float}>  $coordsLatLng
     * @param  list<float>  $segLensMeters
     */
    private function pointAtDistanceAlong(array $coordsLatLng, array $segLensMeters, float $distanceMeters): array
    {
        $remaining = $distanceMeters;
        $idx = 0;
        foreach ($segLensMeters as $len) {
            if ($remaining <= $len) {
                $a = $coordsLatLng[$idx];
                $b = $coordsLatLng[$idx + 1];
                $t = $len > 0 ? ($remaining / $len) : 0;

                return [
                    $a[0] + ($b[0] - $a[0]) * $t,
                    $a[1] + ($b[1] - $a[1]) * $t,
                ];
            }
            $remaining -= $len;
            $idx++;
        }

        return $coordsLatLng[count($coordsLatLng) - 1];
    }

    /**
     * @param  array{0: float, 1: float}  $a
     * @param  array{0: float, 1: float}  $b
     */
    private function segmentMeters(array $a, array $b): float
    {
        return self::haversineMeters($a[0], $a[1], $b[0], $b[1]);
    }

    public static function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $x = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);

        return $earth * (2 * atan2(sqrt($x), sqrt(1 - $x)));
    }
}
