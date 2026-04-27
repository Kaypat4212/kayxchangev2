<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's KX tag.
     */
    public function updateKxTag(Request $request): RedirectResponse
    {
        $request->validate([
            'kx_tag' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique(User::class, 'kx_tag')->ignore($request->user()->id),
            ],
        ], [
            'kx_tag.regex'  => 'Tag may only contain letters, numbers, and underscores.',
            'kx_tag.unique' => 'That tag is already taken. Please choose another.',
            'kx_tag.min'    => 'Tag must be at least 3 characters.',
            'kx_tag.max'    => 'Tag may not be longer than 20 characters.',
        ]);

        $request->user()->update(['kx_tag' => strtolower($request->kx_tag)]);

        return Redirect::route('profile.edit')->with('status', 'tag-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
