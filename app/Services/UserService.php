<?php

namespace App\Services;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserService
{
    public function __construct(private readonly UserRepositoryInterface $repo) {}

    public function all(): Collection { return $this->repo->all(); }
    public function paginate(int $perPage = 15): LengthAwarePaginator { return $this->repo->paginate($perPage); }

    public function findOrFail(int $id): User {
        $m = $this->repo->find($id);
        abort_if(!$m, 404, 'User not found.');
        return $m;
    }

    public function store(array $data): User { return $this->repo->create($data); }

    public function update(int $id, array $data): User {
        $m = $this->findOrFail($id);
        return $this->repo->update($m, $data);
    }

    public function destroy(int $id): void {
        $m = $this->findOrFail($id);
        $this->repo->delete($m);
    }
}
