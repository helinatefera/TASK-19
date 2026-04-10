<?php

namespace App\Http\Controllers\Api\Campaign;

use App\Enums\CampaignStatus;
use App\Enums\CampaignVisibility;
use App\Http\Controllers\Api\BaseController;
use App\Models\VenueProgram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VenueProgramController extends BaseController
{
    /**
     * GET /api/programs
     */
    public function index(Request $request): JsonResponse
    {
        $query = VenueProgram::query()
            ->with(['timeSlots', 'creator'])
            ->latest();

        // Moderators/admins can filter by any status (e.g. pending_review for approval queue)
        $user = $request->user();
        $isPrivileged = $user && ($user->hasRole('moderator') || $user->hasRole('admin'));

        if ($isPrivileged && $request->filled('status')) {
            $query->where('status', $request->query('status'));
        } else {
            // Public listing: only published + online
            $query->where('status', CampaignStatus::Published)
                ->where('visibility', CampaignVisibility::Online);
        }

        if ($request->filled('search')) {
            $query->where('title', 'ilike', '%' . $request->query('search') . '%');
        }

        return $this->paginated($query);
    }

    /**
     * GET /api/programs/{program}
     */
    public function show(Request $request, VenueProgram $program): JsonResponse
    {
        // Public users can only view published + online programs
        $user = $request->user();
        $isPrivileged = $user && ($user->hasRole('moderator') || $user->hasRole('admin') || $user->id === $program->created_by);

        if (! $isPrivileged) {
            if ($program->status !== CampaignStatus::Published || $program->visibility !== CampaignVisibility::Online) {
                return $this->error('Resource not found.', 404);
            }
        }

        $program->load('timeSlots');

        return $this->success($program);
    }

    /**
     * POST /api/programs
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', VenueProgram::class);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'sometimes|string|max:255',
        ]);

        $program = VenueProgram::create([
            'title' => $request->input('title'),
            'slug' => Str::slug($request->input('title')) . '-' . Str::random(6),
            'description' => $request->input('description'),
            'location' => $request->input('location'),
            'status' => CampaignStatus::Draft,
            'visibility' => CampaignVisibility::Offline,
            'created_by' => $request->user()->id,
        ]);

        return $this->success($program, 201);
    }

    /**
     * PUT /api/programs/{program}
     */
    public function update(Request $request, VenueProgram $program): JsonResponse
    {
        $this->authorize('update', $program);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'location' => 'sometimes|string|max:255',
        ]);

        $program->update($request->only(['title', 'description', 'location']));

        return $this->success($program->refresh());
    }

    /**
     * POST /api/programs/{program}/submit
     */
    public function submit(VenueProgram $program): JsonResponse
    {
        $this->authorize('submit', $program);

        $program->update(['status' => CampaignStatus::PendingReview]);

        return $this->success($program->refresh());
    }

    /**
     * POST /api/programs/{program}/approve
     */
    public function approve(Request $request, VenueProgram $program): JsonResponse
    {
        $this->authorize('approve', $program);

        $program->update([
            'status' => CampaignStatus::Published,
            'visibility' => CampaignVisibility::Online,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return $this->success($program->refresh());
    }

    /**
     * POST /api/programs/{program}/reject
     */
    public function reject(Request $request, VenueProgram $program): JsonResponse
    {
        $this->authorize('reject', $program);

        $request->validate([
            'notes' => 'required|string',
        ]);

        $program->update([
            'status' => CampaignStatus::Draft,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $request->input('notes'),
        ]);

        return $this->success($program->refresh());
    }

    /**
     * POST /api/programs/{program}/visibility
     */
    public function visibility(Request $request, VenueProgram $program): JsonResponse
    {
        $this->authorize('toggleVisibility', $program);

        $request->validate([
            'visibility' => 'required|string|in:online,offline',
        ]);

        $program->update([
            'visibility' => CampaignVisibility::from($request->input('visibility')),
        ]);

        return $this->success($program->refresh());
    }
}
