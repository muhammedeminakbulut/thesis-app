<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Model;

class InvalidRepository implements RepositoryInterface
{
    /**
     * @var string
     */
    private $remote;

    /**
     * Repository constructor.
     * @param string $remote
     */
    public function __construct(string $remote)
    {
        $this->remote = $remote;
    }
    /**
     * @return string
     */
    public function getRemote(): string
    {
        return $this->remote;
    }

    /**
     * @return string
     */
    public function getLocalPath(): ?string
    {
        throw new \BadFunctionCallException('This function should not be called');
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        throw new \BadFunctionCallException('This function should not be called');
    }
}
