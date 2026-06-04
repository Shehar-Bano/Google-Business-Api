<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrivacyPolicyController extends Controller
{
    public function edit(): View
    {
        $privacyPolicy = ContentPage::firstOrCreate(
            ['slug' => 'privacy-policy'],
            [
                'title' => 'Privacy Policy',
                'content' => '',
                'is_active' => true,
            ]
        );

        return view('content.admin.privacy-policy.edit', compact('privacyPolicy'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $privacyPolicy = ContentPage::firstOrCreate(
            ['slug' => 'privacy-policy'],
            [
                'title' => 'Privacy Policy',
                'content' => '',
                'is_active' => true,
            ]
        );

        $privacyPolicy->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.privacy-policy.edit')->with('success', 'Privacy policy updated successfully.');
    }
}
