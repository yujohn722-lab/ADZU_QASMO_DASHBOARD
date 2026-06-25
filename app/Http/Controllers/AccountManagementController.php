<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountManagementController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->orderByRaw("role = 'admin' desc")
            ->orderBy('name')
            ->get();

        return view('admin.accounts.index', [
            'users' => $users,
        ]);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 403, 'You cannot remove your own account.');

        if ($user->isAdmin()) {
            $adminCount = User::query()
                ->where('role', 'admin')
                ->count();

            abort_if($adminCount <= 1, 403, 'At least one admin account must remain.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('accounts.index')
            ->with('status', $name.' has been removed.');
    }
}
