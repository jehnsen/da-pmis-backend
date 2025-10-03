<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserService $service) {}

    public function index(Request $request)
    {
        $perPage = (int)$request->query('per_page', 0);
        return $perPage > 0
            ? UserResource::collection($this->service->paginate($perPage))
            : UserResource::collection($this->service->all());
    }

    public function show(int $id)
    {
        return new UserResource($this->service->findOrFail($id));
    }

    public function store(StoreUserRequest $request)
    {
        $m = $this->service->store($request->validated());
        return (new UserResource($m))->response()->setStatusCode(201);
    }

    public function update(UpdateUserRequest $request, int $id)
    {
        $m = $this->service->update($id, $request->validated());
        return new UserResource($m);
    }

    public function destroy(int $id)
    {
        $this->service->destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
