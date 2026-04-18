<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteContent;
use Illuminate\Http\Request;

class SiteContentController extends Controller
{
    public function index()
    {
        $sections = SiteContent::orderBy('group')->orderBy('id')->get()->groupBy('group');
        return view('admin.site-content.index', compact('sections'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'content'   => 'required|array',
            'content.*' => 'nullable|string|max:5000',
        ]);

        foreach ($data['content'] as $key => $value) {
            SiteContent::where('key', $key)->update(['value' => $value]);
        }

        return back()->with('success', 'Homepage content updated successfully.');
    }
}
