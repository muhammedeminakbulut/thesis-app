<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\SOLID\Sniffs\SingleResponsibility;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class InterfacesSniff
 *
 * When a class misses an interface it can be an indication it is not bound to a form of contract.
 * This can indicate it can have multiple reasons to change.
 * Not such a strong indication thus will result in a warning.
 */
class InterfacesSniff implements Sniff
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