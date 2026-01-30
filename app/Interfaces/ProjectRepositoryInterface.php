<?php

namespace App\Interfaces;

interface ProjectRepositoryInterface
{
    public function all(array $filters = []);

    public function find($id, $user = null);

    public function create(array $data);

    public function update($id, array $data, $user = null);

    public function delete($id, $user = null);

    public function paginate(int $perPage = 15, array $filters = [], $user = null);

    public function withRelations($id, array $relations = []);
}
