<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVideoRequest;
use App\Http\Requests\Admin\UpdateVideoRequest;
use App\Models\Video;
use App\Services\Admin\VideoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function __construct(private readonly VideoService $videoService)
    {
    }

    public function index(Request $request): View
    {
        return view('content.admin.videos.index', $this->videoService->indexData($request));
    }

    public function create(): View
    {
        return view('content.admin.videos.create');
    }

    public function store(StoreVideoRequest $request): RedirectResponse
    {
        $this->videoService->create($request);

        return redirect()->route('admin.videos.index')->with('success', 'Video short link created successfully.');
    }

    public function edit(Video $video): View
    {
        return view('content.admin.videos.edit', compact('video'));
    }

    public function update(UpdateVideoRequest $request, Video $video): RedirectResponse
    {
        $this->videoService->update($request, $video);

        return redirect()->route('admin.videos.index')->with('success', 'Video short link updated successfully.');
    }

    public function destroy(Video $video): RedirectResponse
    {
        $this->videoService->delete($video);

        return redirect()->route('admin.videos.index')->with('success', 'Video short link deleted successfully.');
    }
}
