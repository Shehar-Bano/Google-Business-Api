<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClubController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 10) : 10;

        $sort = in_array($request->string('sort', 'created_at')->toString(), ['club_name', 'name', 'email', 'city', 'created_at'], true)
            ? $request->string('sort', 'created_at')->toString() : 'created_at';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $search = trim($request->string('search')->toString());
        $status = trim($request->string('status')->toString());

        $clubs = User::query()
            ->where('role', 'club')
            ->withCount('courts')
            ->when($search !== '', fn (Builder $q) =>
                $q->where(fn (Builder $sub) =>
                    $sub->where('club_name', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                )
            )
            ->when($status !== '', fn (Builder $q) => $q->where('status', $status))
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('content.admin.clubs.index', compact('clubs', 'search', 'status', 'sort', 'direction', 'perPage'));
    }

    public function create(): View
    {
        return view('content.admin.clubs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'club_name'        => ['required', 'string', 'max:255'],
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'unique:users,email'],
            'phone'            => ['nullable', 'string', 'max:25', 'unique:users,phone'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
            'city'             => ['nullable', 'string', 'max:100'],
            'address'          => ['nullable', 'string', 'max:255'],
            'number_of_courts' => ['nullable', 'integer', 'min:1'],
            'working_hours'    => ['nullable', 'string', 'max:100'],
            'status'           => ['required', 'in:otp_pending,pending,active,rejected,suspended'],
            'club_logo'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $validated['password']     = Hash::make($validated['password']);
        $validated['role']         = 'club';
        $validated['otp_verified'] = true;

        if ($request->hasFile('club_logo')) {
            $validated['club_logo'] = $request->file('club_logo')->store('club-logos', 'public');
        }

        User::create($validated);

        return redirect()->route('admin.clubs.index')->with('success', 'Club created successfully.');
    }

    public function show(User $club): View
    {
        abort_if($club->role !== 'club', 404);
        $club->load('courts');

        return view('content.admin.clubs.show', compact('club'));
    }

    public function edit(User $club): View
    {
        abort_if($club->role !== 'club', 404);

        return view('content.admin.clubs.edit', compact('club'));
    }

    public function update(Request $request, User $club): RedirectResponse
    {
        abort_if($club->role !== 'club', 404);

        $validated = $request->validate([
            'club_name'        => ['required', 'string', 'max:255'],
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', Rule::unique('users')->ignore($club->id)],
            'phone'            => ['nullable', 'string', 'max:25', Rule::unique('users')->ignore($club->id)],
            'city'             => ['nullable', 'string', 'max:100'],
            'address'          => ['nullable', 'string', 'max:255'],
            'number_of_courts' => ['nullable', 'integer', 'min:1'],
            'working_hours'    => ['nullable', 'string', 'max:100'],
            'status'           => ['required', 'in:otp_pending,pending,active,rejected,suspended'],
            'club_logo'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('club_logo')) {
            if ($club->club_logo) {
                Storage::disk('public')->delete($club->club_logo);
            }
            $validated['club_logo'] = $request->file('club_logo')->store('club-logos', 'public');
        }

        $club->update($validated);

        return redirect()->route('admin.clubs.index')->with('success', 'Club updated successfully.');
    }

    public function updateStatus(Request $request, User $club): RedirectResponse
    {
        abort_if($club->role !== 'club', 404);

        $request->validate([
            'status' => ['required', 'in:otp_pending,pending,active,rejected,suspended'],
        ]);

        $club->update(['status' => $request->input('status')]);

        return redirect()->back()->with('success', 'Club status updated successfully.');
    }

    public function destroy(User $club): RedirectResponse
    {
        abort_if($club->role !== 'club', 404);

        if ($club->club_logo) {
            Storage::disk('public')->delete($club->club_logo);
        }

        $club->delete();

        return redirect()->route('admin.clubs.index')->with('success', 'Club deleted successfully.');
    }
}
