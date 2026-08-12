<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiGeneratedPoster;
use App\Services\AiGeneratedPosterService;
use Illuminate\Http\Request;

class AiGeneratedPosterController extends Controller
{
    public function __construct(protected AiGeneratedPosterService $aiPosterService) {}

    /**
     * Display a listing of AI generated posters.
     */
    public function index(Request $request)
    {
        $search = trim($request->string('search')->toString());
        $status = trim($request->string('status')->toString());
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 15, 20, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 10) : 10;

        $sort = in_array(
            $request->string('sort', 'created_at')->toString(),
            ['id', 'user', 'business', 'prompt', 'status', 'created_at'],
            true
        ) ? $request->string('sort', 'created_at')->toString() : 'created_at';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $posters = $this->aiPosterService->paginateGenerated($perPage, $search, $sort, $direction, $status);

        return view('content.admin.ai-generated-posters.index', compact('posters', 'search', 'perPage', 'sort', 'direction', 'status'));
    }

    /**
     * Display details of a specific AI generated poster.
     */
    public function show(AiGeneratedPoster $aiGeneratedPoster)
    {
        $aiGeneratedPoster->load(['user', 'business', 'poster', 'approver', 'latestSocialPublish']);
        return view('content.admin.ai-generated-posters.show', compact('aiGeneratedPoster'));
    }

    /**
     * Approve AI Generated Poster.
     */
    public function approve(Request $request, AiGeneratedPoster $aiGeneratedPoster)
    {
        $adminId = auth()->id();
        $scheduledAt = null;
        if ($request->filled('scheduled_at')) {
            try {
                $scheduledAt = \Carbon\Carbon::parse($request->input('scheduled_at'));
            } catch (\Throwable $e) {}
        }

        $this->aiPosterService->approvePoster($aiGeneratedPoster, $adminId, $scheduledAt);

        $msg = $scheduledAt && $scheduledAt->isFuture()
            ? "AI Generated Poster approved and scheduled for {$scheduledAt->format('Y-m-d H:i')}."
            : 'AI Generated Poster approved and published successfully.';

        return redirect()->route('admin.ai-generated-posters.index')
            ->with('success', $msg);
    }

    /**
     * Reject AI Generated Poster.
     */
    public function reject(Request $request, AiGeneratedPoster $aiGeneratedPoster)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000',
        ]);

        $this->aiPosterService->rejectPoster($aiGeneratedPoster, $request->input('rejection_reason'));

        return redirect()->route('admin.ai-generated-posters.index')
            ->with('success', 'AI Generated Poster rejected.');
    }
}
