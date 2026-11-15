<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdminSetting;
use App\Services\DashboardStackService;
use Illuminate\Http\Request;

class DashboardLayoutController extends Controller
{
    public function saveUser(Request $request, DashboardStackService $layouts)
    {
        if ($request->boolean('reset')) {
            $request->user()->forceFill(['dashboard_layout' => null])->save();
            return response()->json(['success' => true, 'message' => 'Your dashboard now follows the admin default layout.']);
        }

        $allowed = implode(',', array_keys(DashboardStackService::STACKS));
        $validated = $request->validate([
            'order' => ['required', 'array', 'max:' . count(DashboardStackService::STACKS)],
            'order.*' => ['required', 'string', 'in:' . $allowed],
            'visible' => ['present', 'array', 'min:1', 'max:' . count(DashboardStackService::STACKS)],
            'visible.*' => ['required', 'string', 'in:' . $allowed],
        ]);
        $request->user()->forceFill(['dashboard_layout' => $layouts->normalize($validated)])->save();

        return response()->json(['success' => true, 'message' => 'Your dashboard arrangement has been saved.']);
    }

    public function adminIndex(DashboardStackService $layouts)
    {
        return view('admin.dashboard-layout', [
            'layout' => $layouts->adminDefaults(),
            'stacks' => DashboardStackService::STACKS,
        ]);
    }

    public function saveAdminDefault(Request $request, DashboardStackService $layouts)
    {
        $allowed = implode(',', array_keys(DashboardStackService::STACKS));
        $validated = $request->validate([
            'order' => ['required', 'array', 'max:' . count(DashboardStackService::STACKS)],
            'order.*' => ['required', 'string', 'in:' . $allowed],
            'visible' => ['present', 'array', 'min:1', 'max:' . count(DashboardStackService::STACKS)],
            'visible.*' => ['required', 'string', 'in:' . $allowed],
        ]);
        $normalized = $layouts->normalize($validated);
        AdminSetting::set('dashboard_stack_defaults', json_encode($normalized, JSON_THROW_ON_ERROR));

        return back()->with('success', 'Default dashboard stacks saved. Users with a custom layout keep their arrangement until they reset it.');
    }
}
