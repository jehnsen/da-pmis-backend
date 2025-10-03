<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function all(): Collection
    {
        return User::latest('id')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->when(request('q'), fn($q,$v) => $q->where('username','like',"%{$v}%")
                ->orWhere('full_name','like',"%{$v}%")
                ->orWhere('email','like',"%{$v}%"))
            ->latest('id')
            ->paginate($perPage);
    }

    public function find(int $id): ?User
    {
        return User::find($id);
    }

    public function findByUsername(string $username): ?User
    {
        return User::where('username',$username)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $model, array $data): User
    {
        $model->update($data);
        return $model;
    }

    public function delete(User $model): void
    {
        $model->delete();
    }
}
