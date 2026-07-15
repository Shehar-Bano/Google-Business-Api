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
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 10) : 10;

        $posters = $this->aiPosterService->paginateGenerated($perPage, $search);

        return view('content.admin.ai-generated-posters.index', compact('posters', 'search', 'perPage'));
    }

    /**
     * Display details of a specific AI generated poster.
     */
    public function show(AiGeneratedPoster $aiGeneratedPoster)
    {
        $aiGeneratedPoster->load(['user', 'business', 'poster', 'approver']);
        return view('content.admin.ai-generated-posters.show', compact('aiGeneratedPoster'));
    }

    /**
     * Approve AI Generated Poster.
     */
    public function approve(AiGeneratedPoster $aiGeneratedPoster)
    {
        $adminId = auth()->id();
        $this->aiPosterService->approvePoster($aiGeneratedPoster, $adminId);

        return redirect()->route('admin.ai-generated-posters.index')
            ->with('success', 'AI Generated Poster approved successfully.');
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
