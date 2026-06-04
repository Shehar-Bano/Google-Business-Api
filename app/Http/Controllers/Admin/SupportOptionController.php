<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SupportOptionController extends Controller
{
    public function index(Request $request): View
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

        $stats = [
            'total' => SupportOption::count(),
            'active' => SupportOption::where('is_active', true)->count(),
            'inactive' => SupportOption::where('is_active', false)->count(),
        ];

        return view('content.admin.support-options.index', compact(
            'supportOptions',
            'stats',
            'search',
            'type',
            'status',
            'sort',
            'direction',
            'perPage'
        ));
    }

    public function create(): View
    {
        return view('content.admin.support-options.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('support-options', 'public');
        }

        SupportOption::create($validated);

        return redirect()->route('admin.support-options.index')->with('success', 'Support option created successfully.');
    }

    public function edit(SupportOption $supportOption): View
    {
        return view('content.admin.support-options.edit', compact('supportOption'));
    }

    public function update(Request $request, SupportOption $supportOption): RedirectResponse
    {
        $validated = $this->validated($request);

        if ($request->hasFile('image')) {
            $this->deleteStoredImage($supportOption->image);
            $validated['image'] = $request->file('image')->store('support-options', 'public');
        }

        $supportOption->update($validated);

        return redirect()->route('admin.support-options.index')->with('success', 'Support option updated successfully.');
    }

    public function destroy(SupportOption $supportOption): RedirectResponse
    {
        $this->deleteStoredImage($supportOption->image);
        $supportOption->delete();

        return redirect()->route('admin.support-options.index')->with('success', 'Support option deleted successfully.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:whatsapp,call,email,chat,website'],
            'value' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function deleteStoredImage(?string $path): void
    {
        if (! $path || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
