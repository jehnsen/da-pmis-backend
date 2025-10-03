<?php

namespace App\Interfaces;

interface CropProductionRepositoryInterface
{
    public function all();

    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function getByRegion($regionId);

    public function getByFiscalYear($fiscalYear);
}
