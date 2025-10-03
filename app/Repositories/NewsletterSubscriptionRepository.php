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

    public function all()
    {
        return $this->model->get();
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
        $subscription->update($data);
        return $subscription;
    }

    public function delete($id)
    {
        $subscription = $this->find($id);
        $subscription->delete();
        return $subscription;
    }

    public function getActive()
    {
        return $this->model->active()->get();
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
