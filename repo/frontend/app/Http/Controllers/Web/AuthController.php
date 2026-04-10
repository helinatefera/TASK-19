<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ApiClient;
use App\Services\Auth\SessionGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $response = $this->apiClient->login(
                $request->input('username'),
                $request->input('password'),
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['username' => 'Invalid credentials.'])->withInput(['username' => $request->input('username')]);
        }

        $data = $response['data'] ?? $response;
        $token = $data['token'] ?? ($response['token'] ?? null);
        $user = $data['user'] ?? ($response['user'] ?? $data);

        /** @var SessionGuard $guard */
        $guard = auth()->guard('web');
        $guard->login($user, $token);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->apiClient->logout();

        /** @var SessionGuard $guard */
        $guard = auth()->guard('web');
        $guard->logout();

        return redirect()->route('login');
    }
}
