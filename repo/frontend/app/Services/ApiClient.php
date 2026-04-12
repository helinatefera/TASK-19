<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Client\Response;
use RuntimeException;

class ApiClient
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.backend.url', 'http://backend-nginx:80'), '/');
    }

    public function get(string $path, array $query = []): array
    {
        $response = $this->request()->get($this->baseUrl . $path, $query);
        return $this->handleResponse($response);
    }

    public function post(string $path, array $data = [], array $headers = []): array
    {
        $request = $this->request();
        if (!empty($headers)) {
            $request = $request->withHeaders($headers);
        }
        $response = $request->post($this->baseUrl . $path, $data);
        return $this->handleResponse($response);
    }

    public function put(string $path, array $data = []): array
    {
        $response = $this->request()->put($this->baseUrl . $path, $data);
        return $this->handleResponse($response);
    }

    public function delete(string $path): array
    {
        $response = $this->request()->delete($this->baseUrl . $path);
        return $this->handleResponse($response);
    }

    public function postWithFile(string $path, array $data = [], string $fileKey = 'attachment', $file = null, array $headers = []): array
    {
        $request = $this->request();
        if (!empty($headers)) {
            $request = $request->withHeaders($headers);
        }

        if ($file) {
            $request = $request->attach(
                $fileKey,
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            );
        }

        $response = $request->post($this->baseUrl . $path, $data);
        return $this->handleResponse($response);
    }

    public function login(string $username, string $password): array
    {
        $response = Http::acceptJson()
            ->post($this->baseUrl . '/api/auth/login', [
                'username' => $username,
                'password' => $password,
            ]);

        return $this->handleResponse($response);
    }

    public function logout(): void
    {
        try {
            $this->post('/api/auth/logout');
        } catch (RuntimeException $e) {
            // Ignore logout errors
        }
    }

    private function request(): \Illuminate\Http\Client\PendingRequest
    {
        $request = Http::acceptJson()->timeout(30);
        $token = Session::get('api_token');
        if ($token) {
            $request = $request->withToken($token);
        }
        return $request;
    }

    private function handleResponse(Response $response): array
    {
        if ($response->successful()) {
            return $response->json() ?? [];
        }

        if ($response->status() === 401) {
            Session::forget(['api_token', 'api_user']);
            throw new RuntimeException('Session expired. Please log in again.', 401);
        }

        $body = $response->json();
        $message = $body['msg'] ?? $body['message'] ?? 'API request failed';
        throw new RuntimeException($message, $response->status());
    }
}
