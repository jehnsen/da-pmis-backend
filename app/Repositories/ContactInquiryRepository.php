<?php

namespace App\Repositories;

use App\Models\ContactInquiry;
use App\Interfaces\ContactInquiryRepositoryInterface;

class ContactInquiryRepository implements ContactInquiryRepositoryInterface
{
    protected $model;

    public function __construct(ContactInquiry $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [])
    {
        $query = $this->model->query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = $this->model->query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $inquiry = $this->find($id);
        if ($inquiry) {
            $inquiry->update($data);
            return $inquiry->fresh();
        }
        return null;
    }

    public function delete($id)
    {
        $inquiry = $this->find($id);
        if ($inquiry) {
            $inquiry->delete();
            return $inquiry;
        }
        return null;
    }

    public function getPending()
    {
        return $this->model->pending()->orderBy('created_at', 'desc')->get();
    }

    public function getResponded()
    {
        return $this->model->responded()->orderBy('created_at', 'desc')->get();
    }
}
