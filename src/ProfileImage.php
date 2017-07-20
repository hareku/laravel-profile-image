<?php

namespace Hareku\LaravelProfileImage;

use Exception;
use Hareku\LaravelProfileImage\Config\{Configs, ModelConfig, Type};
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;

class ProfileImage implements ProfileImageContract
{
    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var FileSystem
     */
    protected $fileSystem;

    /**
     * @var CacheRepository
     */
    protected $cache;

    /**
     * @var Configs
     */
    protected $configs;

    /**
     * Create a new instance.
     *
     * @param  ImageManager  $imageManager
     * @param  Filesystem  $fileSystem
     * @param  CacheRepository  $cache
     * @param  Configs  $configs
     * @return void
     */
    public function __construct(
        ImageManager $imageManager,
        Filesystem $fileSystem,
        CacheRepository $cache,
        Configs $configs
    )
    {
        $this->imageManager = $imageManager;
        $this->fileSystem   = $fileSystem;
        $this->cache        = $cache;
        $this->configs      = $configs;
    }

    /**
     * Upload profile image to temporary storage.
     *
     * @param  UploadedFile  $file
     * @param  string  $modelClass
     * @param  mixed  $id
     * @return void
     */
    public function uploadToTemporaryStorage(UploadedFile $file, string $modelClass, $id): void
    {
        $file->move(
            $this->configs->temporaryDirectory(),
            $this->resolveModelConfig($modelClass)->temporaryImageName($id)
        );
    }

    /**
     * Upload profile images to storage.
     *
     * @param  string  $modelClass
     * @param  mixed  $id
     * @return void
     */
    public function uploadToStorage(string $modelClass, $id): void
    {
        $modelConfig = $this->resolveModelConfig($modelClass);
        $temporaryFilePath = $this->configs->temporaryDirectory().
                            '/'.
                            $modelConfig->temporaryImageName($id);

        $resource = fopen($temporaryFilePath, 'r+');

        $image = $this->imageManager->make($resource);
        $image->backup();

        foreach ($modelConfig->types() as $type) {
            if ($size = $type->size()) {
                $image->resize($size, $size);
            }
            $image->encode($modelConfig->extension());
            $this->fileSystem->put($this->imagePath($modelConfig, $type, $id), $image->__toString());

            $image->reset();
        }

        $image->destroy();

        unlink($temporaryFilePath);
    }

    /**
     * Get a image file path in storage.
     *
     * @param  ModelConfig  $modelConfig
     * @param  Type  $type
     * @param  mixed  $id
     * @return string
     */
    protected function imagePath(ModelConfig $modelConfig, Type $type, $id = null): string
    {
        $fileName = $id
                    ? "{$id}.".$modelConfig->extension()
                    : $modelConfig->defaultImageName();

        return $modelConfig->directory().
                '/'.
                $type->directory().
                '/'.
                $fileName;
    }

    /**
     * Get a profile images url set.
     *
     * @param  string  $modelClass
     * @param  mixed  $id
     * @return array
     */
    public function urlSet(string $modelClass, $id): array
    {
        $modelConfig = $this->resolveModelConfig($modelClass);

        if (! $this->hasProfileImage($modelConfig, $id)) {
            return $this->defaultUrlSet($modelClass);
        }

        $urlSet = [];

        foreach ($modelConfig->types() as $type) {
            $urlSet[$type->name()] = $this->url($modelConfig, $type, $id);
        }

        return $urlSet;
    }

    /**
     * Get a default user profile images url set.
     *
     * @param  string  $modelClass
     * @return array
     */
    public function defaultUrlSet(string $modelClass): array
    {
        $modelConfig = $this->configs->get($modelClass);
        $urlSet = [];

        foreach ($modelConfig->types() as $type) {
            $urlSet[$type->name()] = $this->defaultUrl($modelConfig, $type);
        }

        return $urlSet;
    }

    /**
     * Get a profile image url.
     *
     * @param  ModelConfig  $modelConfig
     * @param  Type  $type
     * @param  mixed  $id
     * @param  bool|null  $hasProfileImage
     * @return string
     */
    protected function url(ModelConfig $modelConfig, Type $type, $id): string
    {
        return $this->fileSystem->url($this->imagePath($modelConfig, $type, $id));
    }

    /**
     * Get a default profile image url.
     *
     * @param  ModelConfig  $modelConfig
     * @param  Type  $type
     * @return string
     */
    protected function defaultUrl(ModelConfig $modelConfig, Type $type): string
    {
        return $this->fileSystem->url($this->imagePath($modelConfig, $type, null));
    }

    /**
     * Delete all types user profile image from storage.
     *
     * @param  string  $modelClass
     * @param  mixed  $id
     * @return bool
     */
    public function deleteAllTypes(string $modelClass, $id): bool
    {
        return $this->fileSystem->delete($this->paths($modelClass, $id));
    }

    /**
     * Get user profile images paths.
     *
     * @param  string  $modelClass
     * @param  mixed  $id
     * @return array
     */
    public function paths(string $modelClass, $id): array
    {
        $modelConfig = $this->resolveModelConfig($modelClass);
        $paths = [];

        foreach ($modelConfig->types() as $type) {
            $paths[] = $this->imagePath($modelConfig, $type, $id);
        }

        return $paths;
    }

    /**
     * Check if an icon exists by cache store.
     *
     * @param  string|ModelConfig  $modelConfig
     * @param  mixed  $id
     * @return bool
     */
    public function hasProfileImage($modelConfig, $id): bool
    {
        $modelConfig = $this->resolveModelConfig($modelConfig);

        return $this->cache->rememberForever(
            $this->hasProfileImageCacheKey($modelConfig, $id), function () use ($modelConfig, $id) {
                return $this->iconExists($modelConfig, $id);
            }
        );
    }

    /**
     * Set the 'hasProfileImage' value.
     *
     * @param  string|ModelConfig  $modelConfig
     * @param  mixed  $id
     * @param  bool  $has
     * @return void
     */
    public function setHasProfileImage($modelConfig, $id, bool $has): void
    {
        $modelConfig = $this->resolveModelConfig($modelConfig);
        $this->cache->forever($this->hasProfileImageCacheKey($modelConfig, $id), $has);
    }

    /**
     * Forget the 'hasProfileImage'.
     *
     * @param  string  $modelConfig
     * @param  mixed  $id
     * @return void
     */
    public function forgetHasProfileImage(string $modelClass, $id): void
    {
        $modelConfig = $this->resolveModelConfig($modelClass);
        $this->cache->forget($this->hasProfileImageCacheKey($modelConfig, $id));
    }

    /**
     * Get caching key for 'has profile image'.
     *
     * @param  ModelConfig  $modelConfig
     * @param  mixed  $id
     * @return string
     */
    protected function hasProfileImageCacheKey(ModelConfig $modelConfig, $id): string
    {
        return 'hasProfileImage_'.$modelConfig->identifier().":{$id}";
    }

    /**
     * Check if an icon exists.
     *
     * @param  ModelConfig  $modelConfig
     * @param  mixed  $id
     * @return bool
     */
    protected function iconExists(ModelConfig $modelConfig, $id): bool
    {
        return $this->fileSystem->exists(
            $this->imagePath($modelConfig, array_first($modelConfig->types()), $id)
        );
    }

    /**
     * Resolve a model config.
     *
     * @param  string|ModelConfig  $modelConfig
     * @return ModelConfig
     */
    protected function resolveModelConfig($modelConfig): ModelConfig
    {
        return $modelConfig instanceof ModelConfig
            ? $modelConfig
            : $this->configs->get($modelConfig);
    }
}
