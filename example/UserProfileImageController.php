<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Hareku\LaravelProfileImage\ProfileImageContract as ProfileImage;
use Illuminate\Http\Request;

class UserProfileImageController extends Controller
{
    /**
     * @var ProfileImage
     */
    protected $profileImage;

    /**
     * Create a new controller instance.
     *
     * @param  ProfileImage  $profileImage
     * @return void
     */
    public function __construct(ProfileImage $profileImage)
    {
        $this->profileImage = $profileImage;

        $this->middleware('auth:api');
    }

    /**
     * Upload the requested user profile image.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadUserProfileImage(Request $request)
    {
        $this->validate($request, [
            'image' => 'required|file|image',
        ]);

        $user = $request->user();
        $this->profileImage->uploadToTemporaryStorage($request->file('image'), get_class($user), $user->id);

        // You can move this upload/resize process to the job.
        // It is recommended for your app response speed.
        $this->profileImage->uploadToStorage(get_class($user), $user->id);

        $urlSet = $this->profileImage->urlSet(get_class($user), $user->id);

        return response()->json(['url_set' => $urlSet], 201);
    }

    /**
     * Delete user profile images.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUserProfileImage(Request $request)
    {
        $user = $request->user();
        $this->profileImage->delete(get_class($user), $user->id);

        $defaultUrlSet = $this->profileImage->defaultUrlSet(get_class($user));

        return response()->json(['default_url_set' => $defaultUrlSet]);
    }
}
