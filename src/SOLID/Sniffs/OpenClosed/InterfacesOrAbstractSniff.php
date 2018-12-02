<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\SOLID\Sniffs\OpenClosed;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class InterfacesOrAbstractSniff
 *
 * A class with Interfaces or itself would be abstract is an indication it is Open.
 * A class which is not Open is an violation of the OpenClosed Principle.
 * A class missing Interfaces and which is not Abstract would result in an error.
 */
class InterfacesOrAbstractSniff implements Sniff
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