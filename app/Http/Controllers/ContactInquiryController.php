<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactInquiry\StoreContactInquiryRequest;
use App\Http\Requests\ContactInquiry\UpdateContactInquiryRequest;
use App\Http\Resources\ContactInquiryResource;
use App\Services\ContactInquiryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactInquiryController extends Controller
{
    public function __construct(private readonly ContactInquiryService $service)
    {
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $filters = $request->only(['status']);
        $data = $this->service->list($perPage, $filters);
        return ContactInquiryResource::collection($data);
    }

    public function store(StoreContactInquiryRequest $request): JsonResponse
    {
        try {
            $inquiry = $this->service->create($request->validated());
            return (new ContactInquiryResource($inquiry))
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create contact inquiry', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $contactInquiry): ContactInquiryResource
    {
        $inquiry = $this->service->getById($contactInquiry);
        abort_unless($inquiry, 404);
        return new ContactInquiryResource($inquiry);
    }

    public function update(UpdateContactInquiryRequest $request, int $contactInquiry): JsonResponse
    {
        try {
            $inquiry = $this->service->update($contactInquiry, $request->validated());
            return (new ContactInquiryResource($inquiry))
                ->response();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update contact inquiry', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(int $contactInquiry): JsonResponse
    {
        abort_unless($this->service->delete($contactInquiry), 404);
        return response()->json(['message' => 'Contact inquiry deleted successfully']);
    }
}
