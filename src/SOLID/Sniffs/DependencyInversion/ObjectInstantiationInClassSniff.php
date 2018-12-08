<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\SOLID\Sniffs\DependencyInversion;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class ObjectInstantiationInClassSniff
 *
 * When a object is instantiated with the keyword `new` this is an violation of the Dependency Inversion principle
 * This will result in an error.
 */
class ObjectInstantiationInClassSniff implements Sniff
{
    const ERROR_MESSAGE = 'Dependency Inversion principle violation: an object is created with `new` try to inject it.';

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
        $line = $stackPtr;
        while ($line = $phpcsFile->findNext([T_NEW], $line)) {
            $phpcsFile->addError(self::ERROR_MESSAGE, $line, 'NewKeyword');
            $line++;
        }
    }
}