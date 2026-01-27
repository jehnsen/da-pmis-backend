<?php

namespace App\Http\Controllers;

use App\Http\Resources\MunicipalityResource;
use App\Http\Resources\ProvinceResource;
use App\Http\Resources\RegionResource;
use App\Models\Municipality;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Get all regions
     *
     * GET /api/locations/regions
     */
    public function regions(Request $request): JsonResponse
    {
        try {
            $includeProvinces = $request->boolean('include_provinces', false);
            $activeOnly = $request->boolean('active_only', true);

            $query = Region::query();

            if ($activeOnly) {
                $query->active();
            }

            if ($includeProvinces) {
                $query->with(['provinces' => function ($q) use ($activeOnly) {
                    if ($activeOnly) {
                        $q->active();
                    }
                    $q->orderBy('name');
                }]);
            }

            $regions = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => RegionResource::collection($regions),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve regions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific region with provinces
     *
     * GET /api/locations/regions/{region}
     */
    public function showRegion(Region $region): JsonResponse
    {
        try {
            $region->load(['provinces' => function ($query) {
                $query->active()->orderBy('name');
            }]);

            return response()->json([
                'success' => true,
                'data' => new RegionResource($region),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve region',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get provinces (optionally filtered by region)
     *
     * GET /api/locations/provinces
     */
    public function provinces(Request $request): JsonResponse
    {
        try {
            $regionId = $request->query('region_id');
            $includeMunicipalities = $request->boolean('include_municipalities', false);
            $activeOnly = $request->boolean('active_only', true);

            $query = Province::with('region');

            if ($regionId) {
                $query->inRegion($regionId);
            }

            if ($activeOnly) {
                $query->active();
            }

            if ($includeMunicipalities) {
                $query->with(['municipalities' => function ($q) use ($activeOnly) {
                    if ($activeOnly) {
                        $q->active();
                    }
                    $q->orderBy('name');
                }]);
            }

            $provinces = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => ProvinceResource::collection($provinces),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve provinces',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific province with municipalities
     *
     * GET /api/locations/provinces/{province}
     */
    public function showProvince(Province $province): JsonResponse
    {
        try {
            $province->load([
                'region',
                'municipalities' => function ($query) {
                    $query->active()->orderBy('name');
                },
            ]);

            return response()->json([
                'success' => true,
                'data' => new ProvinceResource($province),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve province',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get municipalities (optionally filtered by province)
     *
     * GET /api/locations/municipalities
     */
    public function municipalities(Request $request): JsonResponse
    {
        try {
            $provinceId = $request->query('province_id');
            $regionId = $request->query('region_id');
            $type = $request->query('type'); // municipality, city, etc.
            $activeOnly = $request->boolean('active_only', true);
            $perPage = (int) $request->query('per_page', 0); // 0 = no pagination

            $query = Municipality::with('province.region');

            if ($provinceId) {
                $query->inProvince($provinceId);
            }

            if ($regionId) {
                $query->whereHas('province', function ($q) use ($regionId) {
                    $q->where('region_id', $regionId);
                });
            }

            if ($type === 'city') {
                $query->cities();
            } elseif ($type === 'municipality') {
                $query->municipalitiesOnly();
            }

            if ($activeOnly) {
                $query->active();
            }

            $query->orderBy('name');

            if ($perPage > 0) {
                $municipalities = $query->paginate($perPage);

                return response()->json([
                    'success' => true,
                    'data' => MunicipalityResource::collection($municipalities),
                    'meta' => [
                        'current_page' => $municipalities->currentPage(),
                        'last_page' => $municipalities->lastPage(),
                        'per_page' => $municipalities->perPage(),
                        'total' => $municipalities->total(),
                    ],
                ]);
            }

            $municipalities = $query->get();

            return response()->json([
                'success' => true,
                'data' => MunicipalityResource::collection($municipalities),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve municipalities',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific municipality
     *
     * GET /api/locations/municipalities/{municipality}
     */
    public function showMunicipality(Municipality $municipality): JsonResponse
    {
        try {
            $municipality->load('province.region');

            return response()->json([
                'success' => true,
                'data' => new MunicipalityResource($municipality),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve municipality',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the complete location hierarchy
     *
     * GET /api/locations/hierarchy
     */
    public function hierarchy(Request $request): JsonResponse
    {
        try {
            $activeOnly = $request->boolean('active_only', true);

            $query = Region::with([
                'provinces' => function ($q) use ($activeOnly) {
                    if ($activeOnly) {
                        $q->active();
                    }
                    $q->orderBy('name');
                    $q->with(['municipalities' => function ($mq) use ($activeOnly) {
                        if ($activeOnly) {
                            $mq->active();
                        }
                        $mq->orderBy('name');
                    }]);
                },
            ]);

            if ($activeOnly) {
                $query->active();
            }

            $regions = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => RegionResource::collection($regions),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve location hierarchy',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search locations by name
     *
     * GET /api/locations/search
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q', '');

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query must be at least 2 characters',
                ], 422);
            }

            $regions = Region::active()
                ->where('name', 'LIKE', "%{$query}%")
                ->orWhere('code', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get();

            $provinces = Province::active()
                ->with('region')
                ->where('name', 'LIKE', "%{$query}%")
                ->orWhere('code', 'LIKE', "%{$query}%")
                ->limit(10)
                ->get();

            $municipalities = Municipality::active()
                ->with('province.region')
                ->where('name', 'LIKE', "%{$query}%")
                ->orWhere('code', 'LIKE', "%{$query}%")
                ->limit(15)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'regions' => RegionResource::collection($regions),
                    'provinces' => ProvinceResource::collection($provinces),
                    'municipalities' => MunicipalityResource::collection($municipalities),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get location statistics
     *
     * GET /api/locations/statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_regions' => Region::active()->count(),
                    'total_provinces' => Province::active()->count(),
                    'total_municipalities' => Municipality::active()->count(),
                    'total_cities' => Municipality::active()->cities()->count(),
                    'by_region' => Region::active()
                        ->withCount(['provinces' => function ($q) {
                            $q->active();
                        }])
                        ->get()
                        ->map(function ($region) {
                            return [
                                'id' => $region->id,
                                'name' => $region->name,
                                'code' => $region->code,
                                'province_count' => $region->provinces_count,
                            ];
                        }),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
