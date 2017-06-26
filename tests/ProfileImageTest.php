<?php

namespace Hareku\LaravelProfileImage\Tests;

use App\User;
use Hareku\LaravelProfileImage\ProfileImageContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * @group profile-image
 */
class ProfileImageTest extends TestCase
{
    /**
     * @return void
     */
    public function testUploadToTemporaryStorage()
    {
        $file = UploadedFile::fake()->image('profile.jpg');

        resolve(ProfileImageContract::class)
            ->uploadToTemporaryStorage($file, User::class, 1);

        $filePath = storage_path('app/app_user_1.jpg');
        $this->assertFileExists($filePath);

        unlink($filePath);
    }

    /**
     * @return void
     */
    public function testUploadToStorage()
    {
        $class = resolve(ProfileImageContract::class);
        $class->uploadToTemporaryStorage(UploadedFile::fake()->image('profile.jpg'), User::class, 1);
        $class->uploadToStorage(User::class, 1);

        $this->assertFileNotExists(storage_path('app/app_user_1.jpg'));

        foreach ($class->paths(User::class, 1) as $path) {
            $this->assertTrue(Storage::exists($path));
        }
    }

    /**
     * @return void
     */
    public function testUrlSet()
    {
        $class = resolve(ProfileImageContract::class);
        $class->setHasProfileImage(User::class, 1, true);

        $this->assertSame($class->urlSet(User::class, 1), [
            'original_image_url' => '/storage/user-profile-images/original/1.jpg',
            'bigger_image_url' => '/storage/user-profile-images/bigger/1.jpg',
            'normal_image_url' => '/storage/user-profile-images/normal/1.jpg',
            'mini_image_url' => '/storage/user-profile-images/mini/1.jpg',
        ]);
    }

    /**
     * @return void
     */
    public function testDefaultUrlSet()
    {
        $this->assertSame(resolve(ProfileImageContract::class)->defaultUrlSet(User::class), [
            'original_image_url' => '/storage/user-profile-images/original/default.jpg',
            'bigger_image_url' => '/storage/user-profile-images/bigger/default.jpg',
            'normal_image_url' => '/storage/user-profile-images/normal/default.jpg',
            'mini_image_url' => '/storage/user-profile-images/mini/default.jpg',
        ]);
    }

    /**
     * @return void
     */
    public function testPaths()
    {
        $this->assertSame(resolve(ProfileImageContract::class)->paths(User::class, 1), [
            'user-profile-images/original/1.jpg',
            'user-profile-images/bigger/1.jpg',
            'user-profile-images/normal/1.jpg',
            'user-profile-images/mini/1.jpg',
        ]);
    }
}
