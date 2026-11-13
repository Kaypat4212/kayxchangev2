<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use Illuminate\Http\Request;

class AdminOnboardingContentController extends Controller
{
    public function index()
    {
        $content = SiteContent::where('group', 'onboarding')->orderBy('id')->get();
        return view('admin.onboarding-content.index', compact('content'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'content' => ['required', 'array'],
            'content.*' => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($data['content'] as $key => $value) {
            SiteContent::where('group', 'onboarding')->where('key', $key)->update(['value' => $value]);
        }

        return back()->with('success', 'User onboarding content saved.');
    }
}
