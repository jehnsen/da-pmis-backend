<?php

namespace App\Interfaces;

interface ProgressReportRepositoryInterface
{
    public function all(array $filters = []);

    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function paginate(int $perPage = 15, array $filters = []);

    public function createWithMetrics(array $reportData, array $metrics);
}
