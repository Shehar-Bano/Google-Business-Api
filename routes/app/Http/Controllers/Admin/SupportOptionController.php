<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSupportOptionRequest;
use App\Http\Requests\Admin\UpdateSupportOptionRequest;
use App\Models\SupportOption;
use App\Services\Admin\SupportOptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportOptionController extends Controller
{
    public function __construct(private readonly SupportOptionService $supportOptionService)
    {
    }

    public function index(Request $request): View
    {
        return view('content.admin.support-options.index', $this->supportOptionService->indexData($request));
    }

    public function create(): View
    {
        return view('content.admin.support-options.create');
    }

    public function store(StoreSupportOptionRequest $request): RedirectResponse
    {
        $this->supportOptionService->create($request);

        return redirect()->route('admin.support-options.index')->with('success', 'Support option created successfully.');
    }

    public function edit(SupportOption $supportOption): View
    {
        return view('content.admin.support-options.edit', compact('supportOption'));
    }

    public function update(UpdateSupportOptionRequest $request, SupportOption $supportOption): RedirectResponse
    {
        $this->supportOptionService->update($request, $supportOption);

        return redirect()->route('admin.support-options.index')->with('success', 'Support option updated successfully.');
    }

    public function destroy(SupportOption $supportOption): RedirectResponse
    {
        $this->supportOptionService->delete($supportOption);

        return redirect()->route('admin.support-options.index')->with('success', 'Support option deleted successfully.');
    }
}
