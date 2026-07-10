<?php

namespace App\Http\Controllers;

use App\Data\UpdateProfileData;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService,
    ) {}

    public function index(): View
    {
        return view('profiles.index', [
            'profiles' => $this->profileService->getBrowseProfiles(auth()->id()),
        ]);
    }

    public function show(int $id): View
    {
        return view('profiles.show', [
            'profile' => $this->profileService->getProfile($id),
        ]);
    }

    public function edit(): View
    {
        return view('profiles.edit', [
            'profile' => auth()->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = UpdateProfileData::from($request->all());

        $this->profileService->updateProfile(auth()->id(), $data);

        return redirect()->route('profiles.edit')->with('status', 'Profile updated.');
    }
}
