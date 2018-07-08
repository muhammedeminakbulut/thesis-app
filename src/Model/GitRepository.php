<?php
/**
 * Created by PhpStorm.
 * User: muhammed
 * Date: 08-07-18
 * Time: 22:26
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
     * Repository constructor.
     * @param string $remote
     * @param string $localPath
     */
    public function __construct(string $remote, string $localPath = null)
    {
        $this->remote = $remote;
        $this->localPath = $localPath;
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
}
