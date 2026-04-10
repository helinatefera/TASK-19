<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\AuditLog;
use App\Models\BusinessParameter;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends BaseController
{
    /**
     * GET /api/admin/users
     */
    public function listUsers(): JsonResponse
    {
        $query = User::query()->with('roles')->latest();

        return $this->paginated($query);
    }

    /**
     * POST /api/admin/users
     */
    public function createUser(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string|max:60|unique:users,username',
            'password' => 'required|string|min:8',
            'display_name' => 'sometimes|string|max:100',
            'roles' => 'sometimes|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $user = User::create([
            'username' => $request->input('username'),
            'password' => Hash::make($request->input('password')),
            'display_name' => $request->input('display_name', $request->input('username')),
        ]);

        if ($request->filled('roles')) {
            $roleIds = Role::whereIn('name', $request->input('roles'))->pluck('id');
            $user->roles()->attach($roleIds);
        }

        $user->load('roles');

        return $this->success($user, 201);
    }

    /**
     * PUT /api/admin/users/{user}
     */
    public function updateUser(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'display_name' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:8',
            'roles' => 'sometimes|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $updates = [];

        if ($request->filled('display_name')) {
            $updates['display_name'] = $request->input('display_name');
        }

        if ($request->filled('password')) {
            $updates['password'] = Hash::make($request->input('password'));
        }

        if (! empty($updates)) {
            $user->update($updates);
        }

        if ($request->has('roles')) {
            $roleIds = Role::whereIn('name', $request->input('roles'))->pluck('id');
            $user->roles()->sync($roleIds);
        }

        $user->load('roles');

        return $this->success($user->refresh());
    }

    /**
     * GET /api/admin/roles
     */
    public function listRoles(): JsonResponse
    {
        $query = Role::query()->orderBy('name');

        return $this->paginated($query);
    }

    /**
     * GET /api/admin/business-parameters
     */
    public function listBusinessParameters(): JsonResponse
    {
        $query = BusinessParameter::query()->orderBy('key');

        return $this->paginated($query);
    }

    /**
     * PUT /api/admin/business-parameters/{key}
     */
    public function updateBusinessParameter(Request $request, string $key): JsonResponse
    {
        $param = BusinessParameter::where('key', $key)->first();

        if (! $param) {
            return $this->error('Business parameter not found.', 404);
        }

        $request->validate([
            'value' => 'required|string',
        ]);

        $param->update([
            'value' => $request->input('value'),
        ]);

        return $this->success($param->refresh());
    }

    /**
     * GET /api/admin/audit-logs
     */
    public function auditLogs(Request $request): JsonResponse
    {
        $query = AuditLog::query()->with('actor')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->query('actor_id'));
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->query('auditable_type'));
        }

        return $this->paginated($query);
    }
}
