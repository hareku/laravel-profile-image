<?php

namespace Hareku\LaravelProfileImage\Config;

class Type
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    /**
     * @param  string  $name
     * @param  array  $config
     * @return void
     */
    public function __construct(string $name, array $config)
    {
        $this->name   = $name;
        $this->config = $config;
    }

    /**
     * Get the type name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Get the directory.
     *
     * @return string
     */
    public function directory(): string
    {
        return $this->config['directory'];
    }

    /**
     * Get the image size.
     *
     * @return int|null
     */
    public function size(): ?int
    {
        return $this->config['size'];
    }
}
