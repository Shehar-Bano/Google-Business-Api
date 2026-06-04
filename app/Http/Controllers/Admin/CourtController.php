<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourtController extends Controller
{
    public function create(User $club): View
    {
        abort_if($club->role !== 'club', 404);

        return view('content.admin.clubs.courts.form', compact('club'));
    }

    public function store(Request $request, User $club): RedirectResponse
    {
        abort_if($club->role !== 'club', 404);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'type'           => ['required', 'in:glass,wooden,synthetic,other'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'capacity'       => ['nullable', 'integer', 'min:1'],
            'status'         => ['required', 'in:active,inactive,maintenance'],
            'description'    => ['nullable', 'string', 'max:1000'],
        ]);

        $club->courts()->create($validated);

        return redirect()->route('admin.clubs.show', $club)->with('success', 'Court added successfully.');
    }

    public function edit(User $club, Court $court): View
    {
        abort_if($club->role !== 'club' || $court->club_id !== $club->id, 404);

        return view('content.admin.clubs.courts.form', compact('club', 'court'));
    }

    public function update(Request $request, User $club, Court $court): RedirectResponse
    {
        abort_if($club->role !== 'club' || $court->club_id !== $club->id, 404);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'type'           => ['required', 'in:glass,wooden,synthetic,other'],
            'price_per_hour' => ['required', 'numeric', 'min:0'],
            'capacity'       => ['nullable', 'integer', 'min:1'],
            'status'         => ['required', 'in:active,inactive,maintenance'],
            'description'    => ['nullable', 'string', 'max:1000'],
        ]);

        $court->update($validated);

        return redirect()->route('admin.clubs.show', $club)->with('success', 'Court updated successfully.');
    }

    public function destroy(User $club, Court $court): RedirectResponse
    {
        abort_if($club->role !== 'club' || $court->club_id !== $club->id, 404);

        $court->delete();

        return redirect()->route('admin.clubs.show', $club)->with('success', 'Court deleted successfully.');
    }
}
