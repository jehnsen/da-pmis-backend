<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
   public function __construct(private readonly DepartmentService $service)
   {
   }

   public function index(Request $request)
   {
      $perPage = (int) $request->query('per_page', 15);
      $data = $this->service->list($perPage);
      return DepartmentResource::collection($data);
   }

   public function store(StoreDepartmentRequest $request): JsonResponse
   {
      $dept = $this->service->create($request->validated());
      return (new DepartmentResource($dept))->response()->setStatusCode(201);
   }

   public function show(int $department): DepartmentResource
   {
      $dept = $this->service->getById($department);
      abort_unless($dept, 404);
      return new DepartmentResource($dept);
   }

   public function update(UpdateDepartmentRequest $request, int $department): DepartmentResource
   {
      $dept = $this->service->update($department, $request->validated());
      return new DepartmentResource($dept);
   }

   public function destroy(int $department): JsonResponse
   {
      abort_unless($this->service->delete($department), 404);
      return response()->json(['message' => 'Deleted']);
   }
}
