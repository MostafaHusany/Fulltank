<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\Station;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StationController extends Controller
{
    use ApiResponse;

    protected const DEFAULT_RADIUS_KM = 10;
    protected const MAX_RADIUS_KM = 50;
    protected const EARTH_RADIUS_KM = 6371;
    protected const PER_PAGE = 15;

    public function nearbyStations(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat'          => 'required|numeric|between:-90,90',
            'lng'          => 'required|numeric|between:-180,180',
            'radius'       => 'nullable|numeric|min:1|max:' . self::MAX_RADIUS_KM,
            'fuel_type_id' => 'nullable|integer|exists:fuel_types,id',
            'search'       => 'nullable|string|max:100',
            'per_page'     => 'nullable|integer|min:5|max:50',
        ], [
            'lat.required' => __('api.stations.lat_required'),
            'lat.between'  => __('api.stations.lat_invalid'),
            'lng.required' => __('api.stations.lng_required'),
            'lng.between'  => __('api.stations.lng_invalid'),
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;
        $radius = (float) ($request->radius ?? self::DEFAULT_RADIUS_KM);
        $perPage = (int) ($request->per_page ?? self::PER_PAGE);

        $haversine = sprintf(
            '(%d * ACOS(
                LEAST(1.0, 
                    COS(RADIANS(%f)) * COS(RADIANS(lat)) * COS(RADIANS(lng) - RADIANS(%f)) +
                    SIN(RADIANS(%f)) * SIN(RADIANS(lat))
                )
            ))',
            self::EARTH_RADIUS_KM,
            $lat,
            $lng,
            $lat
        );

        $query = Station::query()
            ->select([
                'stations.id',
                'stations.name',
                'stations.address',
                'stations.lat',
                'stations.lng',
                'stations.nearby_landmarks',
                'stations.phone_1',
                'stations.phone_2',
                'stations.governorate_id',
                'stations.district_id',
            ])
            ->selectRaw("{$haversine} AS distance")
            ->whereNotNull('stations.lat')
            ->whereNotNull('stations.lng')
            ->where('stations.lat', '!=', 0)
            ->where('stations.lng', '!=', 0);

        $query->whereHas('user', function ($q) {
            $q->where('is_active', true);
        });

        if ($request->filled('fuel_type_id')) {
            $query->whereHas('fuelTypes', function ($q) use ($request) {
                $q->where('fuel_types.id', $request->fuel_type_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('stations.name', 'like', "%{$search}%")
                  ->orWhere('stations.address', 'like', "%{$search}%")
                  ->orWhere('stations.nearby_landmarks', 'like', "%{$search}%");
            });
        }

        $query->having('distance', '<=', $radius)
              ->orderBy('distance', 'asc');

        $stations = $query->with([
            'fuelTypes:id,name,price_per_liter',
            'governorate:id,name',
            'district:id,name',
        ])->paginate($perPage);

        $formattedStations = $stations->getCollection()->map(function ($station) {
            return $this->formatStation($station);
        });

        return $this->success([
            'stations'   => $formattedStations,
            'pagination' => [
                'current_page' => $stations->currentPage(),
                'last_page'    => $stations->lastPage(),
                'per_page'     => $stations->perPage(),
                'total'        => $stations->total(),
                'has_more'     => $stations->hasMorePages(),
            ],
            'search_params' => [
                'lat'    => $lat,
                'lng'    => $lng,
                'radius' => $radius,
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $station = Station::with([
            'fuelTypes:id,name,price_per_liter',
            'governorate:id,name',
            'district:id,name',
        ])->find($id);

        if (!$station) {
            return $this->notFound(__('api.stations.not_found'));
        }

        $distance = null;
        if ($request->filled('lat') && $request->filled('lng')) {
            $distance = $this->calculateDistance(
                (float) $request->lat,
                (float) $request->lng,
                (float) $station->lat,
                (float) $station->lng
            );
        }

        $formattedStation = $this->formatStation($station, $distance);

        return $this->success($formattedStation);
    }

    public function fuelTypes(Request $request): JsonResponse
    {
        $fuelTypes = \App\Models\FuelType::where('is_active', true)
            ->select(['id', 'name', 'price_per_liter'])
            ->orderBy('name')
            ->get()
            ->map(function ($type) {
                return [
                    'id'             => $type->id,
                    'name'           => $type->name,
                    'price_per_liter' => (float) $type->price_per_liter,
                ];
            });

        return $this->success([
            'fuel_types' => $fuelTypes,
        ]);
    }

    protected function formatStation(Station $station, ?float $distance = null): array
    {
        $distanceValue = $distance ?? ($station->distance ?? null);

        $formattedDistance = null;
        if ($distanceValue !== null) {
            if ($distanceValue < 1) {
                $formattedDistance = [
                    'value'     => round($distanceValue * 1000),
                    'unit'      => 'm',
                    'formatted' => round($distanceValue * 1000) . ' m',
                ];
            } else {
                $formattedDistance = [
                    'value'     => round($distanceValue, 1),
                    'unit'      => 'km',
                    'formatted' => round($distanceValue, 1) . ' km',
                ];
            }
        }

        $fuelTypes = $station->fuelTypes->map(function ($type) {
            return [
                'id'             => $type->id,
                'name'           => $type->name,
                'price_per_liter' => (float) $type->price_per_liter,
            ];
        });

        return [
            'id'               => $station->id,
            'name'             => $station->name,
            'address'          => $station->address,
            'nearby_landmarks' => $station->nearby_landmarks,
            'location'         => [
                'latitude'  => (float) $station->lat,
                'longitude' => (float) $station->lng,
            ],
            'contact' => [
                'phone_1' => $station->phone_1,
                'phone_2' => $station->phone_2,
            ],
            'governorate'  => $station->governorate?->name,
            'district'     => $station->district?->name,
            'fuel_types'   => $fuelTypes,
            'distance'     => $formattedDistance,
        ];
    }

    protected function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $lng1Rad = deg2rad($lng1);
        $lng2Rad = deg2rad($lng2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLng = $lng2Rad - $lng1Rad;

        $a = sin($deltaLat / 2) ** 2 +
             cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) ** 2;

        $c = 2 * asin(sqrt($a));

        return self::EARTH_RADIUS_KM * $c;
    }
}
