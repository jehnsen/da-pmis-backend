<?php

namespace App\Interfaces;

interface ProgressReportRepositoryInterface
{
    public function all();

    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function getByDepartment($departmentId);

    public function getByPeriod($period);
}
