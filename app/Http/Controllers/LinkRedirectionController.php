<?php

namespace App\Http\Controllers;

use App\Models\ReviewRequest;
use Illuminate\Http\RedirectResponse;

class LinkRedirectionController extends Controller
{
    /**
     * Track user click and redirect to the Google Review page.
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function redirect(int $id): RedirectResponse
    {
        $reviewRequest = ReviewRequest::findOrFail($id);

        $userAgent = request()->header('User-Agent', '');

        // Detect WhatsApp/Meta crawlers or other search bots to avoid false clicks
        $isCrawler = preg_match('/facebookexternalhit|whatsapp|bot|spider|crawl/i', $userAgent);

        if (!$isCrawler) {
            // Update tracking status and timestamp
            $reviewRequest->update([
                'clicked_at' => now(),
                'status' => 'clicked',
            ]);
        }

        $redirectionUrl = $reviewRequest->redirection_url ?: 'https://search.google.com/local/writereview?placeid=' . ($reviewRequest->business->google_place_id ?? '');

        return redirect()->away($redirectionUrl);
    }
}
