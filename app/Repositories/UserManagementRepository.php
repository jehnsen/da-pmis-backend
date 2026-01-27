<?php

namespace App\Repositories;

use App\Interfaces\UserManagementRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserManagementRepository implements UserManagementRepositoryInterface
{
    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [])
    {
        $query = $this->model->query()->with(['role', 'department']);

        $this->applyFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function find($id)
    {
        return $this->model->with(['role', 'department'])->find($id);
    }

    public function create(array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $user = $this->find($id);
        if ($user) {
            if (isset($data['password']) && ! empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            return $user->fresh()->load(['role', 'department']);
        }

        return null;
    }

    public function delete($id)
    {
        $user = $this->find($id);
        if ($user) {
            $user->delete();

            return $user;
        }

        return null;
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = $this->model->query()->with(['role', 'department']);

        $this->applyFilters($query, $filters);

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $allowedSortFields = ['full_name', 'username', 'email', 'created_at', 'last_login_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }

    public function toggleStatus($id)
    {
        $user = $this->find($id);
        if ($user) {
            $user->is_active = ! $user->is_active;
            $user->save();

            return $user->fresh()->load(['role', 'department']);
        }

        return null;
    }

    public function getStatistics()
    {
        // Users by role
        $byRole = $this->model->query()
            ->select('roles.name', DB::raw('COUNT(users.id) as count'))
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->groupBy('roles.id', 'roles.name')
            ->pluck('count', 'name')
            ->toArray();

        // Users by department
        $byDepartment = $this->model->query()
            ->select('departments.name', DB::raw('COUNT(users.id) as count'))
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->groupBy('departments.id', 'departments.name')
            ->pluck('count', 'name')
            ->toArray();

        // Active/Inactive counts
        $activeCount = $this->model->where('is_active', true)->count();
        $inactiveCount = $this->model->where('is_active', false)->count();

        // Recent logins
        $recentLogins = $this->model->query()
            ->with(['role', 'department'])
            ->whereNotNull('last_login_at')
            ->orderBy('last_login_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'role' => $user->role->name ?? null,
                    'last_login_at' => $user->last_login_at?->toIso8601String(),
                ];
            });

        return [
            'by_role' => $byRole,
            'by_department' => $byDepartment,
            'active_count' => $activeCount,
            'inactive_count' => $inactiveCount,
            'total_count' => $activeCount + $inactiveCount,
            'recent_logins' => $recentLogins,
        ];
    }

    public function getSummary(array $filters = [])
    {
        $query = $this->model->query();

        // Apply same filters for consistency
        $this->applyFilters($query, $filters);

        $totalUsers = (clone $query)->count();
        $activeUsers = (clone $query)->where('is_active', true)->count();

        // Count administrators
        $administrators = (clone $query)
            ->whereHas('role', function ($q) {
                $q->where('name', 'like', '%admin%');
            })
            ->count();

        // Count unique departments
        $departments = (clone $query)
            ->whereNotNull('department_id')
            ->distinct('department_id')
            ->count('department_id');

        return [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'administrators' => $administrators,
            'departments' => $departments,
        ];
    }

    public function updateLastLogin($id)
    {
        $user = $this->model->find($id);
        if ($user) {
            $user->last_login_at = now();
            $user->save();

            return $user;
        }

        return null;
    }

    protected function applyFilters($query, array $filters)
    {
        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('username', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Role filter
        if (isset($filters['role_id'])) {
            $query->where('role_id', $filters['role_id']);
        }

        // Department filter
        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        // Status filter
        if (isset($filters['status'])) {
            $isActive = $filters['status'] === 'active';
            $query->where('is_active', $isActive);
        }

        // Is active filter (boolean)
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
    }
}
