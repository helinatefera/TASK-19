<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\IntegrationStub;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationStubController extends BaseController
{
    public function index(): JsonResponse
    {
        return $this->paginated(IntegrationStub::query()->orderBy('name'));
    }

    public function show(IntegrationStub $integrationStub): JsonResponse
    {
        return $this->success($integrationStub);
    }

    public function update(Request $request, IntegrationStub $integrationStub): JsonResponse
    {
        $request->validate([
            'is_active' => 'required|boolean',
            'config' => 'sometimes|array',
        ]);

        $integrationStub->update($request->only(['is_active', 'config']));

        return $this->success($integrationStub->refresh());
    }
}
