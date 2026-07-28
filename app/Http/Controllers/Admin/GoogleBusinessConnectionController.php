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

        $search = trim($request->string('search')->toString());

        // Get businesses with keywords and user context
        $connections = Business::query()
            ->with(['user.socialAccounts', 'keywordIdeas'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
            })
            ->paginate($perPage)
            ->withQueryString();

        return view('content.admin.google-business.connections', compact('connections', 'search', 'perPage'));
    }
}
