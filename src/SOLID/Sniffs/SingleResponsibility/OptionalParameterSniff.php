<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\SOLID\Sniffs\SingleResponsibility;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class OptionalParameterSniff
 *
 * This sniff can report an error if it finds a class with a function which haves a optional argument.
 * public function something($foo = true) should register an error.
 * Because the function and so the class has more then one reason to change.
 *
 */
class OptionalParameterSniff implements Sniff
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        return [T_CLASS];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $functionStart = $phpcsFile->findNext(
            [T_FUNCTION],
            $stackPtr - 1,
            null,
            true
        );
    }
}