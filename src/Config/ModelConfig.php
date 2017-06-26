<?php

namespace Hareku\LaravelProfileImage\Config;

class ModelConfig
{
    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param  string  $modelClass
     * @param  array  $modelConfig
     * @return void
     */
    public function __construct(string $modelClass, array $modelConfig)
    {
        $this->modelClass = $modelClass;

        $this->setConfig($modelConfig);
    }

    /**
     * Set the profile image model configuration.
     *
     * @param  array  $modelConfig
     * @return void
     */
    protected function setConfig(array $modelConfig): void
    {
        $modelConfig['types'] = $this->wrapTypes($modelConfig['types']);

        $this->config = $modelConfig;
    }

    /**
     * Wrap image types in getter class.
     *
     * @param  array  $types
     * @return array
     */
    protected function wrapTypes(array $types): array
    {
        $wrappedTypes = [];

        foreach ($types as $typeName => $typeConfig) {
            $wrappedTypes[$typeName] = new Type($typeName, $typeConfig);
        }

        return $wrappedTypes;
    }

    /**
     * Get a model class.
     *
     * @return string
     */
    public function class(): string
    {
        return $this->modelClass;
    }

    /**
     * Get image types.
     *
     * @return array
     */
    public function types(): array
    {
        return $this->config['types'];
    }

    /**
     * Get a type.
     *
     * @param  string  $typeName
     * @return Type
     */
    public function getType(string $typeName): Type
    {
        return $this->types()[$typeName];
    }

    /**
     * Get a image extension for storing to Filesystem.
     *
     * @return string
     */
    public function extension(): string
    {
        return $this->config['extension'];
    }

    /**
     * Get a directory for storing to Filesystem.
     *
     * @return string
     */
    public function directory(): string
    {
        return $this->config['directory'];
    }

    /**
     * Get a name of default profile image.
     *
     * @return string
     */
    public function defaultImageName(): string
    {
        return $this->config['default_image_name'];
    }

    /**
     * Get a model identifier.
     *
     * @return string
     */
    public function identifier(): string
    {
        return snake_case(str_replace('\\', '', $this->modelClass));
    }

    /**
     * Get a temporary file name.
     *
     * @param  mixed  $id
     * @return string
     */
    public function temporaryImageName($id): string
    {
        return $this->identifier()."_{$id}.".$this->extension();
    }
}
