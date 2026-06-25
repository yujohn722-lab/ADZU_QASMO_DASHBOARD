<?php

namespace App\Http\Controllers;

use App\Models\ReportNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function open(Request $request, ReportNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 403);

        $notification->update(['read_at' => now()]);
        $review = $notification->review;

        if (! $review) {
            return redirect()->route('dashboard');
        }

        if ($review->module_key === 'responder-approvals') {
            return redirect()->route('responder-approvals.index');
        }

        return redirect()->route($review->module_key.'.show', $review->reportable_id);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        ReportNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('status', 'Notifications marked as read.');
    }
}
