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
        $businessName = trim($request->string('business_name')->toString());
        $location = trim($request->string('location')->toString());
        $dateFrom = trim($request->string('date_from')->toString());
        $dateTo = trim($request->string('date_to')->toString());

        $perPage = in_array((int) $request->integer('per_page', 25), [10, 25, 50, 100], true)
            ? (int) $request->integer('per_page', 25) : 25;

        $sort = in_array(
            $request->string('sort', 'total_score')->toString(),
            ['name', 'total_score', 'updated_at'],
            true
        ) ? $request->string('sort', 'total_score')->toString() : 'total_score';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $businesses = \App\Models\Business::query()
            ->with(['estimatedScores' => function ($q) {
                $q->orderBy('name', 'asc');
            }])
            ->withSum('estimatedScores as total_score', 'points')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($businessName !== '', function ($query) use ($businessName) {
                $query->where('name', 'like', "%{$businessName}%");
            })
            ->when($location !== '', function ($query) use ($location) {
                $query->where('location', 'like', "%{$location}%");
            })
            ->when($dateFrom !== '', function ($query) use ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo !== '', function ($query) use ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->when($sort === 'name', function ($query) use ($direction) {
                $query->orderBy('name', $direction);
            })
            ->when($sort === 'total_score', function ($query) use ($direction) {
                $query->orderBy('total_score', $direction);
            })
            ->when($sort === 'updated_at', function ($query) use ($direction) {
                $query->orderBy('updated_at', $direction);
            })
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total_businesses' => \App\Models\Business::count(),
            'total_points' => (int) BusinessEstimatedScore::sum('points'),
        ];

        return view('content.admin.estimated-scores.index', compact(
            'businesses', 'stats', 'search', 'businessName', 'location', 'dateFrom', 'dateTo', 'perPage', 'sort', 'direction'
        ));
    }
}
