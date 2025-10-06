<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterSubscription\StoreNewsletterSubscriptionRequest;
use App\Http\Requests\NewsletterSubscription\UpdateNewsletterSubscriptionRequest;
use App\Http\Resources\NewsletterSubscriptionResource;
use App\Services\NewsletterSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterSubscriptionController extends Controller
{
    public function __construct(private readonly NewsletterSubscriptionService $service)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $filters = $request->only(['is_active']);
        $data = $this->service->list($perPage, $filters);
        return NewsletterSubscriptionResource::collection($data);
    }

    public function store(StoreNewsletterSubscriptionRequest $request): JsonResponse
    {
        try {
            $subscription = $this->service->create($request->validated());
            return (new NewsletterSubscriptionResource($subscription))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create newsletter subscription', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $newsletterSubscription): NewsletterSubscriptionResource
    {
        $subscription = $this->service->getById($newsletterSubscription);
        abort_unless($subscription, 404);
        return new NewsletterSubscriptionResource($subscription);
    }

    public function update(UpdateNewsletterSubscriptionRequest $request, int $newsletterSubscription): JsonResponse
    {
        try {
            $subscription = $this->service->update($newsletterSubscription, $request->validated());
            return (new NewsletterSubscriptionResource($subscription))
                ->response();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update newsletter subscription', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $newsletterSubscription): JsonResponse
    {
        abort_unless($this->service->delete($newsletterSubscription), 404);
        return response()->json(['message' => 'Newsletter subscription deleted successfully']);
    }
}
