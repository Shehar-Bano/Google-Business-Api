<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessEstimatedScore;
use Illuminate\Http\Request;

class EstimatedScoreController extends Controller
{
    /**
     * Display a listing of estimated scores with filtering.
     */
    public function index(Request $request)
    {
        $search = trim($request->string('search')->toString());
        $perPage = in_array((int) $request->integer('per_page', 25), [10, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 25) : 25;

        $scores = BusinessEstimatedScore::with('business')
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('business', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('name', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total_records' => BusinessEstimatedScore::count(),
            'total_points' => BusinessEstimatedScore::sum('points'),
        ];

        return view('content.admin.estimated-scores.index', compact(
            'scores', 'stats', 'search', 'perPage'
        ));
    }
}
