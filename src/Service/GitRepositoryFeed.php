<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Service;

use App\Model\GitRepoUrl;

class GitRepositoryFeed
{
    /**
     * @var array|GitRepoUrl[]
     */
    private $repositories;

    /**
     * GitHubRepositoryFeed constructor.
     * @var string feed
     */
    public function __construct($feed)
    {
        $result = json_decode(file_get_contents($feed), true);
        $this->repositories = array_map(
            function ($array) {
                return new GitRepoUrl($array['full_name'], $array['ssh_url']);
            },
            $result['items']
        );
    }

    /**
     * @return array|GitRepoUrl[]
     */
    public function getRepositories(): array
    {
        return $this->repositories;
    }
}