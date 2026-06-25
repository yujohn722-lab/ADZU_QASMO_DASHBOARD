<?php

namespace App\Http\Controllers;

use App\Models\ReportReview;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResponderApprovalController extends Controller
{
    public function index(): View
    {
        $pendingResponders = User::query()
            ->where('role', 'respondent')
            ->whereNull('approved_at')
            ->latest()
            ->get();

        return view('admin.responder-approvals.index', [
            'pendingResponders' => $pendingResponders,
        ]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === 'respondent', 404);
        abort_if($user->isApproved(), 404);

        $user->forceFill(['approved_at' => now()])->save();
        $this->registrationReviewFor($user)?->update([
            'status' => 'accepted',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('responder-approvals.index')
            ->with('status', $user->name.' has been approved.');
    }

    public function reject(User $user): RedirectResponse
    {
        abort_unless($user->role === 'respondent', 404);
        abort_if($user->isApproved(), 404);

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('responder-approvals.index')
            ->with('status', $name.' has been rejected.');
    }

    private function registrationReviewFor(User $user): ?ReportReview
    {
        return ReportReview::query()
            ->where('module_key', 'responder-approvals')
            ->where('reportable_type', User::class)
            ->where('reportable_id', $user->id)
            ->first();
    }
}
