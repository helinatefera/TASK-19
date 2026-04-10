<?php

namespace App\Livewire\Admin;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class BusinessParameterEditor extends Component
{
    public array $editValues = [];

    protected ApiClient $apiClient;

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function mount(): void
    {
        $this->loadParameters();
    }

    public function loadParameters(): void
    {
        try {
            $response = $this->apiClient->get('/api/admin/business-parameters');
            $parameters = $response['data'] ?? $response;
            $this->editValues = [];

            foreach ($parameters as $param) {
                $this->editValues[$param['key']] = $param['value'];
            }
        } catch (\RuntimeException $e) {
            $this->editValues = [];
            session()->flash('error', $e->getMessage());
        }
    }

    public function save(string $key): void
    {
        $value = $this->editValues[$key] ?? '';

        try {
            $this->apiClient->put("/api/admin/business-parameters/{$key}", [
                'value' => $value,
            ]);

            session()->flash('success', "Parameter '{$key}' updated successfully.");
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(): View
    {
        try {
            $response = $this->apiClient->get('/api/admin/business-parameters');
            $parameters = $response['data'] ?? $response;
        } catch (\RuntimeException $e) {
            $parameters = [];
            session()->flash('error', $e->getMessage());
        }

        return view('livewire.admin.business-parameter-editor', [
            'parameters' => $parameters,
        ]);
    }
}
