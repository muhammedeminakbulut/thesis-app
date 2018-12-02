<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\SOLID\Sniffs\SingleResponsibility;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class MultiplePublicFunctionsSniff
 *
 * A class with multiple public functions has multiple reasons to change.
 *
 * This sniff registers an error when a class has multiple public functions.
 */
class MultiplePublicFunctionsSniff implements Sniff
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