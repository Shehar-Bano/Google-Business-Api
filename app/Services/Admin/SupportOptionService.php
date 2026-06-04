<?php

namespace App\Services\Admin;

use App\Http\Requests\Admin\StoreSupportOptionRequest;
use App\Http\Requests\Admin\UpdateSupportOptionRequest;
use App\Models\SupportOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupportOptionService
{
    public function indexData(Request $request): array
    {
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 10) : 10;

        $sort = in_array($request->string('sort', 'sort_order')->toString(), ['title', 'type', 'is_active', 'sort_order', 'created_at'], true)
            ? $request->string('sort', 'sort_order')->toString() : 'sort_order';

        $direction = in_array($request->string('direction', 'asc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'asc')->toString() : 'asc';

        $search = trim($request->string('search')->toString());
        $type = trim($request->string('type')->toString());
        $status = trim($request->string('status')->toString());

        $supportOptions = SupportOption::query()
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('value', 'like', "%{$search}%");
                });
            })
            ->when($type !== '', fn (Builder $query) => $query->where('type', $type))
            ->when($status !== '', fn (Builder $query) => $query->where('is_active', $status === 'active'))
            ->orderBy($sort, $direction)
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return [
            'supportOptions' => $supportOptions,
            'stats' => [
                'total' => SupportOption::count(),
                'active' => SupportOption::where('is_active', true)->count(),
                'inactive' => SupportOption::where('is_active', false)->count(),
            ],
            'search' => $search,
            'type' => $type,
            'status' => $status,
            'sort' => $sort,
            'direction' => $direction,
            'perPage' => $perPage,
        ];
    }

    public function create(StoreSupportOptionRequest $request): SupportOption
    {
        $validated = $request->payload();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('support-options', 'public');
        }

        return SupportOption::create($validated);
    }

    public function update(UpdateSupportOptionRequest $request, SupportOption $supportOption): SupportOption
    {
        $validated = $request->payload();

        if ($request->hasFile('image')) {
            $this->deleteStoredImage($supportOption->image);
            $validated['image'] = $request->file('image')->store('support-options', 'public');
        }

        $supportOption->update($validated);

        return $supportOption;
    }

    public function delete(SupportOption $supportOption): void
    {
        $this->deleteStoredImage($supportOption->image);
        $supportOption->delete();
    }

    private function deleteStoredImage(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
