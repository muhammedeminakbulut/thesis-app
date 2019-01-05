<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\Model;

interface RepositoryInterface
{
    /**
     * @return string
     */
    public function getRemote(): string;

    /**
     * @return string
     */
    public function getLocalPath(): ?string;

    public function getData(): array;
}
