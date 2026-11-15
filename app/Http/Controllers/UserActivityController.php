<?php

namespace App\Http\Controllers;

use App\Services\UserActivityFeedService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{
    public function index(Request $request, UserActivityFeedService $feed)
    {
        $all = $feed->forUser($request->user(), 100);
        $perPage = 25;
        $page = LengthAwarePaginator::resolveCurrentPage('page');
        $activities = new LengthAwarePaginator(
            $all->forPage($page, $perPage)->values(),
            $all->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('activity.index', compact('activities'));
    }
}
