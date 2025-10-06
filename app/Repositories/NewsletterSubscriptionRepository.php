<?php

namespace App\Repositories;

use App\Models\NewsletterSubscription;
use App\Interfaces\NewsletterSubscriptionRepositoryInterface;

class NewsletterSubscriptionRepository implements NewsletterSubscriptionRepositoryInterface
{
    protected $model;

    public function __construct(NewsletterSubscription $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = [])
    {
        $query = $this->model->query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->get();
    }

    public function paginate(int $perPage = 15, array $filters = [])
    {
        $query = $this->model->query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->paginate($perPage);
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
        $subscription = $this->find($id);
        if ($subscription) {
            $subscription->update($data);
            return $subscription->fresh();
        }
        return null;
    }

    public function delete($id)
    {
        $subscription = $this->find($id);
        if ($subscription) {
            $subscription->delete();
            return $subscription;
        }
        return null;
    }

    public function getActive()
    {
        return $this->model->active()->get();
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function subscribe($email)
    {
        return $this->model->updateOrCreate(
            ['email' => $email],
            ['is_active' => true, 'subscribed_at' => now()]
        );
    }

    public function unsubscribe($email)
    {
        $subscription = $this->model->where('email', $email)->first();
        if ($subscription) {
            $subscription->update(['is_active' => false]);
            return $subscription;
        }
        return null;
    }
}
