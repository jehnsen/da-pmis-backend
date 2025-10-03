<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?User;
    public function findByUsername(string $username): ?User;
    public function create(array $data): User;
    public function update(User $model, array $data): User;
    public function delete(User $model): void;
}
