<?php

namespace App\Http\Controllers;

use App\Models\ReportNotification;
use App\Models\ReportReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportReviewController extends Controller
{
    public function updateStatus(Request $request, ReportReview $reportReview): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'status' => ['required', Rule::in(['accepted', 'rejected', 'changes_requested'])],
            'admin_message' => ['nullable', 'required_if:status,rejected,changes_requested', 'string', 'max:2000'],
        ]);

        $reportReview->update([
            'status' => $data['status'],
            'admin_message' => $data['admin_message'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        ReportNotification::create([
            'user_id' => $reportReview->respondent_id,
            'report_review_id' => $reportReview->id,
            'type' => 'report_'.$data['status'],
            'message' => $this->respondentMessage($reportReview),
        ]);

        return back()->with('status', 'Report review updated.');
    }

    private function respondentMessage(ReportReview $review): string
    {
        return match ($review->status) {
            'accepted' => $review->module_label.' report #'.$review->reportable_id.' was accepted.',
            'rejected' => $review->module_label.' report #'.$review->reportable_id.' was rejected.',
            'changes_requested' => 'Changes were requested for '.$review->module_label.' report #'.$review->reportable_id.'.',
            default => $review->module_label.' report #'.$review->reportable_id.' review was updated.',
        };
    }
}
