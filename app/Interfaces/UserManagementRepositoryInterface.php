<?php

namespace App\Interfaces;

interface UserManagementRepositoryInterface
{
    public function all(array $filters = []);

    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function paginate(int $perPage = 15, array $filters = []);

    public function toggleStatus($id);

    public function getStatistics();

    public function getSummary(array $filters = []);

    public function updateLastLogin($id);
}
