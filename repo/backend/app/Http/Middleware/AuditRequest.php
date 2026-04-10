<?php

namespace App\Http\Middleware;

use App\Services\Audit\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuditRequest
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->logRequest($request, $response);
        }

        return $response;
    }

    private function logRequest(Request $request, Response $response): void
    {
        try {
            $action = $request->route()?->getName() ?? $request->method() . ' ' . $request->path();
            $user = $request->user();

            $this->auditLogService->log(
                action: $action,
                actor: $user,
                auditableType: 'request',
                auditableId: 0,
                oldValues: null,
                newValues: null,
                metadata: [
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'status' => $response->getStatusCode(),
                    'user_agent' => $request->userAgent(),
                ],
            );
        } catch (\Throwable $e) {
            // If the DB transaction is already aborted (e.g. from event listeners),
            // log the failure but don't propagate — the primary response is more important.
            report($e);
        }
    }
}
