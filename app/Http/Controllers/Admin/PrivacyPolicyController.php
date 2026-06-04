<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePrivacyPolicyRequest;
use App\Services\Admin\PrivacyPolicyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PrivacyPolicyController extends Controller
{
    public function __construct(private readonly PrivacyPolicyService $privacyPolicyService)
    {
    }

    public function edit(): View
    {
        $privacyPolicy = $this->privacyPolicyService->getOrCreate();

        return view('content.admin.privacy-policy.edit', compact('privacyPolicy'));
    }

    public function update(UpdatePrivacyPolicyRequest $request): RedirectResponse
    {
        $this->privacyPolicyService->update($request);

        return redirect()->route('admin.privacy-policy.edit')->with('success', 'Privacy policy updated successfully.');
    }
}
