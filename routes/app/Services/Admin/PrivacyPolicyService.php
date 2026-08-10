<?php

namespace App\Services\Admin;

use App\Http\Requests\Admin\UpdatePrivacyPolicyRequest;
use App\Models\ContentPage;

class PrivacyPolicyService
{
    public function getOrCreate(): ContentPage
    {
        return ContentPage::firstOrCreate(
            ['slug' => 'privacy-policy'],
            [
                'title' => 'Privacy Policy',
                'content' => '',
                'is_active' => true,
            ]
        );
    }

    public function update(UpdatePrivacyPolicyRequest $request): ContentPage
    {
        $privacyPolicy = $this->getOrCreate();
        $validated = $request->validated();

        $privacyPolicy->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return $privacyPolicy;
    }
}
