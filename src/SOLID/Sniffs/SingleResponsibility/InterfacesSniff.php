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
 * When a class misses (an) interface(s) it can be an indication it is not bound to a form of contract.
 * This can indicate it can have multiple reasons to change.
 * Not such a strong indication thus will result in a warning.
 */
class InterfacesSniff implements Sniff
{
    const ERROR_MESSAGE = 'Single Responsibility principle violation: %s class does not implement interfaces.';

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
        $tokens = $phpcsFile->getTokens();

        $classNameToken = $tokens[$phpcsFile->findNext([T_STRING], $stackPtr)];

        if ($phpcsFile->findImplementedInterfaceNames($stackPtr) === false) {
            $phpcsFile->addError(
                sprintf(self::ERROR_MESSAGE, $classNameToken['content']),
                $stackPtr,
                'MissingInterfaces'
            );
        }
    }

}