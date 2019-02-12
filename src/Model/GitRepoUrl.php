<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Model;


class GitRepoUrl
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $url;

    /**
     * @var int|null
     */
    private $forks;

    /**
     * GitRepoUrl constructor.
     * @param string $name
     * @param string $url
     * @param int $forks
     */
    public function __construct(string $name, string $url, int $forks = null)
    {
        $this->name = $name;
        $this->url = $url;
        $this->forks = $forks;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return int
     */
    public function getForks(): int
    {
        return $this->forks;
    }
}