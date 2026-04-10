<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\WebhookDefinition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookDefinitionController extends BaseController
{
    public function index(): JsonResponse
    {
        return $this->paginated(WebhookDefinition::query()->orderBy('name'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => ['required', 'string', 'max:2048', function ($attribute, $value, $fail) {
                $host = parse_url($value, PHP_URL_HOST);
                $allowed = ['localhost', '127.0.0.1', '::1'];
                if (!$host || (!in_array($host, $allowed) && !str_ends_with($host, '.local') && !str_ends_with($host, '.internal'))) {
                    $fail('Webhook URLs must point to localhost or internal hosts only.');
                }
            }],
            'events' => 'required|array|min:1',
            'events.*' => 'string',
            'is_active' => 'sometimes|boolean',
        ]);

        $webhook = WebhookDefinition::create($request->only(['name', 'url', 'events', 'is_active']));

        return $this->success($webhook, 201);
    }

    public function update(Request $request, WebhookDefinition $webhookDefinition): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'url' => ['sometimes', 'string', 'max:2048', function ($attribute, $value, $fail) {
                $host = parse_url($value, PHP_URL_HOST);
                $allowed = ['localhost', '127.0.0.1', '::1'];
                if (!$host || (!in_array($host, $allowed) && !str_ends_with($host, '.local') && !str_ends_with($host, '.internal'))) {
                    $fail('Webhook URLs must point to localhost or internal hosts only.');
                }
            }],
            'events' => 'sometimes|array|min:1',
            'events.*' => 'string',
            'is_active' => 'sometimes|boolean',
        ]);

        $webhookDefinition->update($request->only(['name', 'url', 'events', 'is_active']));

        return $this->success($webhookDefinition->refresh());
    }

    public function destroy(WebhookDefinition $webhookDefinition): JsonResponse
    {
        $webhookDefinition->delete();

        return $this->success(null, 204);
    }
}
