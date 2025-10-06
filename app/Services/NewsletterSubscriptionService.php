<?php

namespace App\Services;

use App\Interfaces\NewsletterSubscriptionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class NewsletterSubscriptionService
{
    public function __construct(private readonly NewsletterSubscriptionRepositoryInterface $repo)
    {
    }

    public function list(int $perPage = 15, array $filters = []): LengthAwarePaginator|Collection
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function create(array $data)
    {
        return $this->repo->create($data);
    }

    public function getById(int $id)
    {
        return $this->repo->find($id);
    }

    public function update(int $id, array $data)
    {
        return $this->repo->update($id, $data);
    }

    public function delete(int $id)
    {
        return $this->repo->delete($id);
    }

    public function getActive()
    {
        return $this->repo->getActive();
    }

    public function findByEmail(string $email)
    {
        return $this->repo->findByEmail($email);
    }

    public function subscribe(string $email)
    {
        return $this->repo->subscribe($email);
    }

    public function unsubscribe(string $email)
    {
        return $this->repo->unsubscribe($email);
    }
}
