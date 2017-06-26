<?php

namespace Hareku\LaravelProfileImage;

use Hareku\LaravelProfileImage\Config\Configs;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;

class ProfileImageServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/profile-image.php' => config_path('profile-image.php'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Configs::class, function ($app) {
            return new Configs;
        });

        $this->app->singleton(ProfileImageContract::class, function ($app) {
            return new ProfileImage(
                $app->make(ImageManager::class),
                $app->make(Filesystem::class),
                $app->make(CacheRepository::class),
                $app->make(Configs::class)
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            Configs::class,
            ProfileImageContract::class,
        ];
    }
}
