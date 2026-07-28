<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GoogleBusinessConnectionController extends Controller
{
    /**
     * Monitor Google Business account connections.
     */
    public function index(Request $request): View
    {
        $perPage = (int) $request->integer('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $sort = in_array(
            $request->string('sort', 'name')->toString(),
            ['name', 'google_place_id', 'isVerified', 'created_at'],
            true
        ) ? $request->string('sort', 'name')->toString() : 'name';

        $direction = in_array($request->string('direction', 'asc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'asc')->toString() : 'asc';

        $search = trim($request->string('search')->toString());

        // Get businesses with keywords and user context
        $connections = Business::query()
            ->with(['user.socialAccounts', 'keywordIdeas'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
            })
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('content.admin.google-business.connections', compact('connections', 'search', 'perPage', 'sort', 'direction'));
    }
}
