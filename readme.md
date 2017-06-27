# Laravel 5 Profile Image System

This package helps you to add profile image system to your project.  
It works if the driver supported by FileSystem. (AWS S3, Rackspace, Public disc)

## Caution
- *Support Laravel 5.4~*  
- *Required php >=7.1*

## Installation

Run `composer require hareku/laravel-profile-image`

Include the service provider within `config/app.php`.

```php
'providers' => [
    Hareku\LaravelProfileImage\ProfileImageServiceProvider::class,
];
```

Publish the config file. (config/profile-image.php)

```sh
$ php artisan vendor:publish --provider="Hareku\LaravelProfileImage\ProfileImageServiceProvider"
```

## Usage

### Example config
```php
<?php

use App\User;

return [
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

```

### Example Contnroller

Example is here. [UserProfileImageController](example/UserProfileImageController.php)

### Get user profile image URL list

```php
$this->profileImage->urlSet(get_class($user), $user->id);

[
    'original_image_url' => '/storage/user-profile-images/original/1.jpg',
    'bigger_image_url' => '/storage/user-profile-images/bigger/1.jpg',
    'normal_image_url' => '/storage/user-profile-images/normal/1.jpg',
    'mini_image_url' => '/storage/user-profile-images/mini/1.jpg',
]
```

## License

MIT

## Author

hareku (hareku908@gmail.com)
