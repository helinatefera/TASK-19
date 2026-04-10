<?php

namespace App\Http\Controllers\Api\Review;

use App\Http\Controllers\Api\BaseController;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\Review;
use App\Services\Review\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

class ReviewController extends BaseController
{
    public function __construct(
        private readonly ReviewService $reviewService,
    ) {}

    /**
     * GET /api/campaigns/{campaign}/reviews
     */
    public function index(Campaign $campaign): JsonResponse
    {
        $query = Review::query()
            ->publiclyVisible()
            ->whereHas('order', function ($q) use ($campaign) {
                $q->where('campaign_id', $campaign->id);
            })
            ->with(['dimensions', 'tags'])
            ->latest();

        $paginator = $query->paginate(
            min((int) request()->query('per_page', 15), 100)
        );

        // Strip real user IDs from public listings to enforce masked-identity protection.
        // The public_alias field provides the only identity visible to consumers.
        $paginator->through(fn (Review $review) => $review->makeHidden(['reviewer_id', 'reviewee_id']));

        return response()->json($paginator);
    }

    /**
     * POST /api/orders/{order}/reviews
     */
    public function store(Request $request, Order $order): JsonResponse
    {
        Gate::authorize('create', [Review::class, $order]);

        $request->validate([
            'side' => 'required|string|in:user_to_creator,creator_to_user',
            'overall_rating' => 'required|integer|min:1|max:5',
            'body' => 'sometimes|string|max:5000',
            'dimensions' => 'sometimes|array',
            'dimensions.*.dimension' => 'required_with:dimensions|string|max:50',
            'dimensions.*.rating' => 'required_with:dimensions|integer|min:1|max:5',
            'tags' => 'sometimes|array',
            'tags.*' => 'string|max:50',
        ]);

        try {
            $review = $this->reviewService->submit($order, $request->user(), [
                'side' => $request->input('side'),
                'overall_rating' => $request->input('overall_rating'),
                'body' => $request->input('body'),
            ]);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        // Create dimension records if provided.
        if ($request->filled('dimensions')) {
            foreach ($request->input('dimensions') as $dimension) {
                $review->dimensions()->create([
                    'dimension' => $dimension['dimension'],
                    'rating' => $dimension['rating'],
                ]);
            }
        }

        // Create tag records if provided.
        if ($request->filled('tags')) {
            foreach ($request->input('tags') as $tag) {
                $review->tags()->create([
                    'tag' => $tag,
                ]);
            }
        }

        $review->load(['dimensions', 'tags']);

        return $this->success($review, 201);
    }
}
