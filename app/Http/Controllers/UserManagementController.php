<?php

namespace App\Http\Controllers;

use App\Classes\ApiResponseClass;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function __construct(private readonly UserManagementService $service)
    {
    }

    /**
     * List all users with filtering and pagination
     *
     * GET /api/users
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);

        $filterKeys = [
            'search',
            'role_id',
            'department_id',
            'status',
            'is_active',
            'sort_by',
            'sort_order',
        ];

        $filters = $request->only($filterKeys);
        $appliedFilters = array_filter($filters, fn ($value) => $value !== null && $value !== '');

        $data = $this->service->list($perPage, $filters);
        $summary = $this->service->getSummary($filters);

        return UserResource::collection($data)->additional([
            'summary' => $summary,
            'filters_applied' => $appliedFilters,
        ]);
    }

    /**
     * Create a new user
     *
     * POST /api/users
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = $this->service->create($request->validated());

            return (new UserResource($user->load(['role', 'department'])))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific user
     *
     * GET /api/users/{id}
     */
    public function show(int $user): JsonResponse
    {
        $userData = $this->service->getById($user);

        if (! $userData) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return (new UserResource($userData->load(['role', 'department'])))
            ->response();
    }

    /**
     * Update a user
     *
     * PUT /api/users/{id}
     */
    public function update(UpdateUserRequest $request, int $user): JsonResponse
    {
        try {
            $userData = $this->service->update($user, $request->validated());

            if (! $userData) {
                return response()->json(['message' => 'User not found'], 404);
            }

            return (new UserResource($userData))
                ->response();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a user
     *
     * DELETE /api/users/{id}
     */
    public function destroy(int $user): JsonResponse
    {
        $deleted = $this->service->delete($user);

        if (! $deleted) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Toggle user active status
     *
     * PATCH /api/users/{id}/toggle-status
     */
    public function toggleStatus(int $user): JsonResponse
    {
        try {
            $userData = $this->service->toggleStatus($user);

            if (! $userData) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $statusText = $userData->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "User {$statusText} successfully",
                'data' => new UserResource($userData),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle user status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user statistics
     *
     * GET /api/users/statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->service->getStatistics();

            return ApiResponseClass::sendResponse($stats, 'User statistics retrieved successfully');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve user statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
