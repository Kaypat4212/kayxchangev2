<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blacklist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlacklistController extends Controller
{
    public function index(Request $request)
    {
        $query = Blacklist::latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where('value', 'like', '%' . $request->search . '%');
        }

        $blacklists = $query->paginate(20)->withQueryString();

        // Resolve user names for user-type entries
        $userIds = $blacklists->where('type', 'user')->pluck('value');
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        return view('admin.blacklist', compact('blacklists', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'   => 'required|in:ip,user',
            'value'  => 'required|string|max:255',
            'reason' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now',
        ]);

        Blacklist::updateOrCreate(
            ['type' => $request->type, 'value' => $request->value],
            [
                'reason'     => $request->reason,
                'blocked_by' => Auth::id(),
                'expires_at' => $request->expires_at,
            ]
        );

        return back()->with('success', ucfirst($request->type) . ' added to blacklist.');
    }

    public function destroy($id)
    {
        Blacklist::findOrFail($id)->delete();
        return back()->with('success', 'Removed from blacklist.');
    }

    public function suspendUser($userId)
    {
        $user = User::findOrFail($userId);
        Blacklist::updateOrCreate(
            ['type' => 'user', 'value' => (string) $userId],
            ['reason' => 'Suspended by admin', 'blocked_by' => Auth::id(), 'expires_at' => null]
        );
        return back()->with('success', $user->name . ' has been suspended.');
    }

    public function unsuspendUser($userId)
    {
        Blacklist::where('type', 'user')->where('value', (string) $userId)->delete();
        return back()->with('success', 'User unsuspended.');
    }
}
