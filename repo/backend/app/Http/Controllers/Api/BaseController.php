<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

abstract class BaseController extends Controller
{
    /**
     * Return a success JSON response.
     */
    protected function success(mixed $data = null, int $code = 200): JsonResponse
    {
        return response()->json($data, $code);
    }

    /**
     * Return a structured error JSON response.
     */
    protected function error(string $msg, int $code = 400): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'msg' => $msg,
        ], $code);
    }

    /**
     * Paginate a query and return a JSON response.
     */
    protected function paginated(Builder $query, int $perPage = 15): JsonResponse
    {
        $paginator = $query->paginate(
            min((int) request()->query('per_page', $perPage), 100)
        );

        return response()->json($paginator);
    }
}
