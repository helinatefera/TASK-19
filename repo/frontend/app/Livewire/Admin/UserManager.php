<?php

namespace App\Livewire\Admin;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class UserManager extends Component
{
    #[Url]
    public string $search = '';

    public int $page = 1;

    public bool $showCreateModal = false;

    public string $newUsername = '';

    public string $newPassword = '';

    public string $newDisplayName = '';

    public array $newRoles = [];

    protected ApiClient $apiClient;

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function updatingSearch(): void
    {
        $this->page = 1;
    }

    public function openCreateModal(): void
    {
        $this->reset(['newUsername', 'newPassword', 'newDisplayName', 'newRoles']);
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function createUser(): void
    {
        $this->validate([
            'newUsername' => 'required|string|min:3|max:255',
            'newPassword' => 'required|string|min:8',
            'newDisplayName' => 'nullable|string|max:255',
        ]);

        $scope = 'idempotency:admin:create-user';
        $key = session($scope);
        if (! $key) {
            $key = 'admin-user-' . uniqid();
            session()->put($scope, $key);
        }

        try {
            $this->apiClient->post('/api/admin/users', [
                'username' => $this->newUsername,
                'password' => $this->newPassword,
                'display_name' => $this->newDisplayName ?: null,
                'roles' => $this->newRoles,
            ], ['X-Idempotency-Key' => $key]);

            session()->forget($scope);
            $this->showCreateModal = false;
            $this->reset(['newUsername', 'newPassword', 'newDisplayName', 'newRoles']);

            session()->flash('success', 'User created successfully.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function updateRoles(int $userId, array $roles): void
    {
        try {
            $this->apiClient->put("/api/admin/users/{$userId}", [
                'roles' => $roles,
            ]);
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function render(): View
    {
        $params = ['page' => $this->page, 'per_page' => 20];

        if ($this->search !== '') {
            $params['search'] = $this->search;
        }

        try {
            $response = $this->apiClient->get('/api/admin/users', $params);
            $users = $response['data'] ?? [];
            $meta = $response['meta'] ?? [];
        } catch (\RuntimeException $e) {
            $users = [];
            $meta = [];
            session()->flash('error', $e->getMessage());
        }

        try {
            $rolesResponse = $this->apiClient->get('/api/admin/roles');
            $allRoles = $rolesResponse['data'] ?? $rolesResponse;
        } catch (\RuntimeException $e) {
            $allRoles = [];
        }

        return view('livewire.admin.user-manager', [
            'users' => $users,
            'allRoles' => $allRoles,
            'meta' => $meta,
        ]);
    }
}
