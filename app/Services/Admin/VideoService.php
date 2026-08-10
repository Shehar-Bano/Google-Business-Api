<?php

namespace App\Services\Admin;

use App\Http\Requests\Admin\StoreVideoRequest;
use App\Http\Requests\Admin\UpdateVideoRequest;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class VideoService
{
    public function indexData(Request $request): array
    {
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 15, 20, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 10) : 10;

        $sort = in_array($request->string('sort', 'screen_name')->toString(), ['screen_name', 'is_active', 'created_at'], true)
            ? $request->string('sort', 'screen_name')->toString() : 'screen_name';

        $direction = in_array($request->string('direction', 'asc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'asc')->toString() : 'asc';

        $search = trim($request->string('search')->toString());
        $status = trim($request->string('status')->toString());

        $videos = Video::query()
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $sub) use ($search) {
                    $sub->where('screen_name', 'like', "%{$search}%")
                        ->orWhere('video_url', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('is_active', $status === 'active'))
            ->orderBy($sort, $direction)
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return [
            'videos' => $videos,
            'stats' => [
                'total' => Video::count(),
                'active' => Video::where('is_active', true)->count(),
                'inactive' => Video::where('is_active', false)->count(),
            ],
            'search' => $search,
            'status' => $status,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
        ];
    }

    public function create(StoreVideoRequest $request): Video
    {
        return Video::create($request->payload());
    }

    public function update(UpdateVideoRequest $request, Video $video): Video
    {
        $video->update($request->payload());

        return $video;
    }

    public function delete(Video $video): void
    {
        $video->delete();
    }
}
