<?php
/**
 * Created by PhpStorm.
 * User: muhammed
 * Date: 11-11-18
 * Time: 12:48
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
     * GitRepoUrl constructor.
     * @param string $name
     * @param string $url
     */
    public function __construct(string $name, string $url)
    {
        $this->name = $name;
        $this->url = $url;
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
}