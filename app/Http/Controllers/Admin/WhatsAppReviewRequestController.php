<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReviewRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppReviewRequestController extends Controller
{
    /**
     * Display a listing of WhatsApp Review Requests with sorting and search.
     */
    public function index(Request $request): View
    {
        $perPage = (int) $request->integer('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $sort = in_array(
            $request->string('sort', 'id')->toString(),
            ['id', 'business', 'sender', 'sent_to_user', 'phone_number', 'status', 'channel', 'created_at'],
            true
        ) ? $request->string('sort', 'id')->toString() : 'id';

        $direction = in_array($request->string('direction', 'desc')->toString(), ['asc', 'desc'], true)
            ? $request->string('direction', 'desc')->toString() : 'desc';

        $search = trim($request->string('search')->toString());
        $status = trim($request->string('status')->toString());
        $channel = trim($request->string('channel')->toString());

        $query = ReviewRequest::query()
            ->select('review_requests.*')
            ->with(['business', 'sender', 'sentToUser']);

        // Handle joins for sorting
        if ($sort === 'business') {
            $query->leftJoin('businesses', 'review_requests.business_id', '=', 'businesses.id')
                  ->orderBy('businesses.name', $direction);
        } elseif ($sort === 'sender') {
            $query->leftJoin('users as senders', 'review_requests.sender_id', '=', 'senders.id')
                  ->orderBy('senders.name', $direction);
        } elseif ($sort === 'sent_to_user') {
            $query->leftJoin('users as recipients', 'review_requests.sent_to', '=', 'recipients.id')
                  ->orderBy('recipients.name', $direction);
        } else {
            $sortColumn = in_array($sort, ['id', 'phone_number', 'status', 'channel', 'created_at'], true) ? $sort : 'id';
            $query->orderBy('review_requests.' . $sortColumn, $direction);
        }

        // Apply search filter
        $query->when($search !== '', function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('review_requests.phone_number', 'like', "%{$search}%")
                    ->orWhere('review_requests.status', 'like', "%{$search}%")
                    ->orWhereHas('business', function ($bq) use ($search) {
                        $bq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sender', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('sentToUser', function ($rq) use ($search) {
                        $rq->where('name', 'like', "%{$search}%");
                    });
            });
        });

        // Apply status filter
        $query->when($status !== '', function ($q) use ($status) {
            $q->where('review_requests.status', $status);
        });

        // Apply channel filter
        $query->when($channel !== '', function ($q) use ($channel) {
            $q->where('review_requests.channel', 'like', "%{$channel}%");
        });

        $requests = $query->paginate($perPage)->withQueryString();

        return view('content.admin.google-business.review-requests', compact('requests', 'search', 'perPage', 'sort', 'direction', 'status', 'channel'));
    }
}
