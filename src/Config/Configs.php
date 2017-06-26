<?php

namespace Hareku\LaravelProfileImage\Config;

use Exception;

class Configs
{
    /**
     * @var array
     */
    protected $configs;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->setConfigs();
    }

    /**
     * Set profile image configurations.
     *
     * @return void
     * @throws \Exception
     */
    protected function setConfigs(): void
    {
        if (! $configs = config('profile-image')) {
            throw new Exception('The profile image configuration was not found.');
        }

        foreach ($configs as $modelClass => $modelConfig) {
            $configs[$modelClass] = new ModelConfig($modelClass, $modelConfig);
        }

        $this->configs = $configs;
    }

    /**
     * Get a configuration of model.
     *
     * @param  string  $modelClass
     * @return ModelConfig
     * @throws \Exception
     */
    public function get(string $modelClass): ModelConfig
    {
        if (! isset($this->configs[$modelClass])) {
            throw new Exception('The specified profile image configuration was not found. Name: '.$modelClass);
        }

        return $this->configs[$modelClass];
    }

    /**
     * Get a temporary directory path.
     *
     * @return string
     */
    public function temporaryDirectory(): string
    {
        return storage_path('app');
    }
}
