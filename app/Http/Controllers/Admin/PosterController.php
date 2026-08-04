<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poster;
use App\Services\PosterService;
use Illuminate\Http\Request;

class PosterController extends Controller
{
    public function __construct(protected PosterService $posterService) {}

    /**
     * Display a listing of poster templates.
     */
    public function index(Request $request)
    {
        $search = trim($request->string('search')->toString());
        $status = trim($request->string('status')->toString());
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 15, 20, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 10) : 10;

        $sort = in_array(
            $request->string('sort', 'created_at')->toString(),
            ['title', 'status', 'created_at'],
            true
        ) ? $request->string('sort', 'created_at')->toString() : 'created_at';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $posters = $this->posterService->paginatePosters($perPage, $search, $sort, $direction, $status);

        return view('content.admin.posters.index', compact('posters', 'search', 'perPage', 'sort', 'direction', 'status'));
    }

    /**
     * Show the form for creating a new poster template.
     */
    public function create()
    {
        return view('content.admin.posters.create');
    }

    /**
     * Store a newly created poster template.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'status' => 'required|in:Active,Inactive',
        ]);

        $this->posterService->storePoster(
            $request->only(['title', 'status']),
            $request->file('image')
        );

        return redirect()->route('admin.posters.index')
            ->with('success', 'Poster template created successfully.');
    }

    /**
     * Display the specified poster template details.
     */
    public function show(Poster $poster)
    {
        return view('content.admin.posters.show', compact('poster'));
    }

    /**
     * Show the form for editing the specified poster template.
     */
    public function edit(Poster $poster)
    {
        return view('content.admin.posters.edit', compact('poster'));
    }

    /**
     * Update the specified poster template.
     */
    public function update(Request $request, Poster $poster)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'status' => 'required|in:Active,Inactive',
        ]);

        $this->posterService->updatePoster(
            $poster,
            $request->only(['title', 'status']),
            $request->file('image')
        );

        return redirect()->route('admin.posters.index')
            ->with('success', 'Poster template updated successfully.');
    }

    /**
     * Remove the specified poster template.
     */
    public function destroy(Poster $poster)
    {
        $this->posterService->deletePoster($poster);

        return redirect()->route('admin.posters.index')
            ->with('success', 'Poster template deleted successfully.');
    }
}
