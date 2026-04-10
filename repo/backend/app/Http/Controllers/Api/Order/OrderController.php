<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Api\BaseController;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\RewardTier;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class OrderController extends BaseController
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    /**
     * GET /api/orders
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Order::query()->with(['campaign', 'rewardTier', 'timeSlot']);

        // Staff/moderator/admin can list all orders; regular users see only their own
        if (! $user->hasRole('staff') && ! $user->hasRole('moderator') && ! $user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('order_type')) {
            $query->where('order_type', $request->query('order_type'));
        }

        return $this->paginated($query->latest());
    }

    /**
     * GET /api/orders/{order}
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load([
            'campaign',
            'rewardTier',
            'timeSlot',
            'payments',
            'vouchers',
            'logisticsMilestones',
            'receipts',
            'refundRequests',
            'afterSalesRequests',
        ]);

        return $this->success($order);
    }

    /**
     * POST /api/orders
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Order::class);

        $request->validate([
            'campaign_id' => 'required|integer|exists:campaigns,id',
            'reward_tier_id' => 'required|integer|exists:reward_tiers,id',
            'request_key' => 'required|string|max:255',
        ]);

        // Enforce blacklist restriction
        $creditScore = $request->user()->creditScore;
        if ($creditScore && ! $creditScore->canPlaceOrders()) {
            return $this->error('Your account is currently restricted from placing orders.', 403);
        }

        $campaign = Campaign::findOrFail($request->input('campaign_id'));
        $tier = RewardTier::findOrFail($request->input('reward_tier_id'));

        try {
            $order = $this->orderService->createContributionOrder(
                $request->user(),
                $campaign,
                $tier,
                $request->input('request_key'),
            );
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success($order, 201);
    }

    /**
     * POST /api/orders/{order}/cancel
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        $this->authorize('cancel', $order);

        $request->validate([
            'reason' => 'sometimes|string|max:1000',
        ]);

        try {
            $order = $this->orderService->cancel(
                $order,
                $request->input('reason', 'Cancelled by user.'),
            );
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success($order);
    }

    /**
     * POST /api/orders/{order}/fulfill
     */
    public function fulfill(Order $order): JsonResponse
    {
        $this->authorize('fulfill', $order);

        try {
            $order = $this->orderService->fulfill($order);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success($order);
    }

    /**
     * POST /api/orders/{order}/attend
     */
    public function attend(Request $request, Order $order): JsonResponse
    {
        $this->authorize('markAttendance', $order);

        $request->validate([
            'attended' => 'required|boolean',
        ]);

        $order->update([
            'attended' => $request->boolean('attended'),
        ]);

        return $this->success($order->refresh());
    }
}
