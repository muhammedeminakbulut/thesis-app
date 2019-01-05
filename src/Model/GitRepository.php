<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Model;

class GitRepository implements RepositoryInterface
{
    /**
     * @var string
     */
    private $remote;

    /**
     * @var string
     */
    private $localPath;

    /**
     * @var array
     */
    private $data;

    /**
     * GitRepository constructor.
     * @param string $remote
     * @param string|null $localPath
     * @param array $data
     */
    public function __construct(string $remote, string $localPath = null, array $data = [])
    {
        $this->remote = $remote;
        $this->localPath = $localPath;
        $this->data = $data;
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
        return $this->localPath;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
