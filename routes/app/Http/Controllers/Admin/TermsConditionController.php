<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TermsCondition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TermsConditionController extends Controller
{
    /**
     * Display a listing of terms and conditions.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('search')->toString());
        $status = trim($request->string('status')->toString());
        $dateFrom = trim($request->string('date_from')->toString());
        $dateTo = trim($request->string('date_to')->toString());

        $perPage = (int) $request->integer('per_page', 10);
        $perPage = in_array($perPage, [10, 15, 20, 25, 50, 100], true) ? $perPage : 10;

        $sort = in_array(
            $request->string('sort', 'id')->toString(),
            ['id', 'title', 'slug', 'status', 'created_at'],
            true
        ) ? $request->string('sort', 'id')->toString() : 'id';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $terms = TermsCondition::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%");
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($dateFrom !== '', fn ($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->whereDate('created_at', '<=', $dateTo))
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        return view('content.admin.terms-conditions.index', compact('terms', 'search', 'status', 'dateFrom', 'dateTo', 'perPage', 'sort', 'direction'));
    }

    /**
     * Show the form for creating a new terms condition.
     */
    public function create(): View
    {
        return view('content.admin.terms-conditions.create');
    }

    /**
     * Store a newly created terms condition in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:terms_conditions,slug',
            'content' => 'required|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $terms = TermsCondition::create([
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'content' => $request->input('content'),
            'status' => $request->input('status'),
        ]);

        \App\Models\AdminAuditLog::log(
            'terms_conditions_create',
            'TermsCondition',
            (string) $terms->id,
            "Created terms and condition page: '{$terms->title}'.",
            ['terms_id' => $terms->id, 'title' => $terms->title]
        );

        return redirect()->route('admin.terms-conditions.index')
            ->with('success', 'Terms & Conditions created successfully.');
    }

    /**
     * Show the form for editing the specified terms condition.
     */
    public function edit(TermsCondition $termsCondition): View
    {
        return view('content.admin.terms-conditions.edit', compact('termsCondition'));
    }

    /**
     * Update the specified terms condition in storage.
     */
    public function update(Request $request, TermsCondition $termsCondition): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:terms_conditions,slug,' . $termsCondition->id,
            'content' => 'required|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $termsCondition->update([
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'content' => $request->input('content'),
            'status' => $request->input('status'),
        ]);

        \App\Models\AdminAuditLog::log(
            'terms_conditions_update',
            'TermsCondition',
            (string) $termsCondition->id,
            "Updated terms and condition page: '{$termsCondition->title}'.",
            ['terms_id' => $termsCondition->id, 'title' => $termsCondition->title]
        );

        return redirect()->route('admin.terms-conditions.index')
            ->with('success', 'Terms & Conditions updated successfully.');
    }

    /**
     * Remove the specified terms condition from storage.
     */
    public function destroy(TermsCondition $termsCondition): RedirectResponse
    {
        $title = $termsCondition->title;
        $id = $termsCondition->id;
        $termsCondition->delete();

        \App\Models\AdminAuditLog::log(
            'terms_conditions_delete',
            'TermsCondition',
            (string) $id,
            "Deleted terms and condition page: '{$title}'.",
            ['terms_title' => $title]
        );

        return redirect()->route('admin.terms-conditions.index')
            ->with('success', 'Terms & Conditions deleted successfully.');
    }
}
