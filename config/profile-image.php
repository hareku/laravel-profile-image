<?php

use App\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel profile image
    |--------------------------------------------------------------------------
    */

    User::class => [

        'extension' => 'jpg',

        'directory' => 'user-profile-images',

        'default_image_name' => 'default.jpg',

        'types' => [

            'original' => [
                'directory' => 'original',
                'size'  => null,
            ],

            'bigger' => [
                'directory' => 'bigger',
                'size'  => 73,
            ],

            'normal' => [
                'directory' => 'normal',
                'size'  => 48,
            ],

            'mini' => [
                'directory' => 'mini',
                'size'  => 24,
            ],

        ],

    ],

];
