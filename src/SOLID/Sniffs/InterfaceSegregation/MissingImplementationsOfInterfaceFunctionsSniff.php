<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\SOLID\Sniffs\InterfaceSegregation;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class MissingImplementationsOfInterfaceFunctionsSniff
 *
 * A class is violating the interface segregation principle when not implementing an interface function. This indicates
 * it depends on other implementations in other classes. This is an error.
 */
class MissingImplementationsOfInterfaceFunctionsSniff implements Sniff
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