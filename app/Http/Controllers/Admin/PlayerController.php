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

class PlayerController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->integer('per_page', 10), [10, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 10) : 10;

        $sort = in_array($request->string('sort', 'created_at')->toString(), ['name', 'email', 'city', 'playing_level', 'created_at'], true)
            ? $request->string('sort', 'created_at')->toString() : 'created_at';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $search       = trim($request->string('search')->toString());
        $status       = trim($request->string('status')->toString());
        $playingLevel = trim($request->string('playing_level')->toString());

        $players = User::query()
            ->where('role', 'player')
            ->when($search !== '', fn (Builder $q) =>
                $q->where(fn (Builder $sub) =>
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                )
            )
            ->when($status !== '', fn (Builder $q) => $q->where('status', $status))
            ->when($playingLevel !== '', fn (Builder $q) => $q->where('playing_level', $playingLevel))
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('content.admin.players.index', compact('players', 'search', 'status', 'playingLevel', 'sort', 'direction', 'perPage'));
    }

    public function create(): View
    {
        return view('content.admin.players.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'unique:users,email'],
            'phone'         => ['nullable', 'string', 'max:25', 'unique:users,phone'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'status'        => ['required', 'in:otp_pending,profile_incomplete,active,suspended'],
            'dob'           => ['nullable', 'date'],
            'gender'        => ['nullable', 'in:male,female,other'],
            'city'          => ['nullable', 'string', 'max:100'],
            'playing_level' => ['nullable', 'in:beginner,intermediate,advanced,professional'],
            'primary_hand'  => ['nullable', 'in:left,right'],
            'bio'           => ['nullable', 'string', 'max:1000'],
            'profile_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $validated['password']     = Hash::make($validated['password']);
        $validated['role']         = 'player';
        $validated['otp_verified'] = true;

        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')->store('player-profiles', 'public');
        }

        User::create($validated);

        return redirect()->route('admin.players.index')->with('success', 'Player created successfully.');
    }

    public function show(User $player): View
    {
        abort_if($player->role !== 'player', 404);

        return view('content.admin.players.show', compact('player'));
    }

    public function edit(User $player): View
    {
        abort_if($player->role !== 'player', 404);

        return view('content.admin.players.edit', compact('player'));
    }

    public function update(Request $request, User $player): RedirectResponse
    {
        abort_if($player->role !== 'player', 404);

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', Rule::unique('users')->ignore($player->id)],
            'phone'         => ['nullable', 'string', 'max:25', Rule::unique('users')->ignore($player->id)],
            'status'        => ['required', 'in:otp_pending,profile_incomplete,active,suspended'],
            'dob'           => ['nullable', 'date'],
            'gender'        => ['nullable', 'in:male,female,other'],
            'city'          => ['nullable', 'string', 'max:100'],
            'playing_level' => ['nullable', 'in:beginner,intermediate,advanced,professional'],
            'primary_hand'  => ['nullable', 'in:left,right'],
            'bio'           => ['nullable', 'string', 'max:1000'],
            'profile_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('profile_image')) {
            if ($player->profile_image) {
                Storage::disk('public')->delete($player->profile_image);
            }
            $validated['profile_image'] = $request->file('profile_image')->store('player-profiles', 'public');
        }

        $player->update($validated);

        return redirect()->route('admin.players.index')->with('success', 'Player updated successfully.');
    }

    public function updateStatus(Request $request, User $player): RedirectResponse
    {
        abort_if($player->role !== 'player', 404);

        $request->validate([
            'status' => ['required', 'in:otp_pending,profile_incomplete,active,suspended'],
        ]);

        $player->update(['status' => $request->input('status')]);

        return redirect()->back()->with('success', 'Player status updated successfully.');
    }

    public function destroy(User $player): RedirectResponse
    {
        abort_if($player->role !== 'player', 404);

        if ($player->profile_image) {
            Storage::disk('public')->delete($player->profile_image);
        }

        $player->delete();

        return redirect()->route('admin.players.index')->with('success', 'Player deleted successfully.');
    }
}
