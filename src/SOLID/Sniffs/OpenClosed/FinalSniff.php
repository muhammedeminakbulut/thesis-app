<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\SOLID\Sniffs\OpenClosed;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class FinalSniff
 *
 * A class which is not Final will result in an error because it is not Closed.
 */
class FinalSniff implements Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        // TODO: Implement process() method.
    }

}