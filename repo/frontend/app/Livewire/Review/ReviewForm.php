<?php

namespace App\Livewire\Review;

use App\Services\ApiClient;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.app')]
class ReviewForm extends Component
{
    protected ApiClient $apiClient;

    public int $orderId = 0;

    public string $side = 'user_to_creator';

    public int $overallRating = 0;

    public string $body = '';

    public array $dimensions = [
        'communication' => 0,
        'accuracy' => 0,
        'value' => 0,
    ];

    public array $tags = [];

    public bool $submitted = false;

    public string $errorMessage = '';

    public array $order = [];

    public function boot(ApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function mount(int $orderId): void
    {
        $this->orderId = $orderId;

        $response = $this->apiClient->get("/api/orders/{$orderId}");

        // Unwrap if wrapped in 'data'
        if (isset($response['data']) && !isset($response['id'])) {
            $this->order = $response['data'];
        } else {
            $this->order = $response;
        }

        if (($this->order['status'] ?? '') !== 'fulfilled') {
            abort(403, 'Reviews can only be submitted for fulfilled orders.');
        }

        $user = Auth::user();

        // Determine default side: if current user is the campaign creator, they review the buyer
        if (!empty($this->order['campaign']) && $user && $user->id === ($this->order['campaign']['creator_id'] ?? null)) {
            $this->side = 'creator_to_user';
        } else {
            $this->side = 'user_to_creator';
        }
    }

    public function setOverallRating(int $rating): void
    {
        $this->overallRating = $rating;
    }

    public function setDimensionRating(string $dimension, int $rating): void
    {
        if (array_key_exists($dimension, $this->dimensions)) {
            $this->dimensions[$dimension] = $rating;
        }
    }

    public function toggleTag(string $tag): void
    {
        if (in_array($tag, $this->tags)) {
            $this->tags = array_values(array_diff($this->tags, [$tag]));
        } else {
            $this->tags[] = $tag;
        }
    }

    public function submit(): void
    {
        $this->errorMessage = '';

        $this->validate([
            'overallRating' => 'required|integer|min:1|max:5',
            'body' => 'nullable|string|max:2000',
            'side' => 'required|in:user_to_creator,creator_to_user',
        ]);

        try {
            $dimensions = [];
            foreach ($this->dimensions as $name => $rating) {
                if ($rating > 0) {
                    $dimensions[] = ['dimension' => $name, 'rating' => (int) $rating];
                }
            }

            $scope = "idempotency:review:{$this->orderId}";
            $key = session($scope);
            if (! $key) {
                $key = 'review-' . $this->orderId . '-' . uniqid();
                session()->put($scope, $key);
            }

            $this->apiClient->post("/api/orders/{$this->orderId}/reviews", [
                'side' => $this->side,
                'overall_rating' => $this->overallRating,
                'body' => $this->body,
                'dimensions' => $dimensions,
                'tags' => $this->tags,
            ], ['X-Idempotency-Key' => $key]);

            session()->forget($scope);
            $this->submitted = true;
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render(): View
    {
        $availableTags = ['friendly', 'late_delivery', 'as_described', 'great_value'];

        return view('livewire.review.review-form', [
            'availableTags' => $availableTags,
        ]);
    }
}
