<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\SOLID\Sniffs\LiskovSubstitution;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class TypeCheckInClassSniff
 *
 * A class in its functions having type checks about which class it is dealing with is breaking the liskov substitution
 * principle thus a type check `instanceOf` or `get_class` will result in an error.
 */
class TypeCheckInClassSniff implements Sniff
{
    const ERROR_MESSAGE = 'Liskov Substitution principle violation: %s used. ';

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_CLASS,
            T_INTERFACE,
            T_ABSTRACT,
            T_TRAIT,
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $line = $stackPtr;
        while ($line = $phpcsFile->findNext([T_INSTANCEOF], $line)) {
            $phpcsFile->addError(sprintf(self::ERROR_MESSAGE, $tokens[$line]['content']), $line, 'InstanceOf');

            $line++;
        }

        $line = $stackPtr;
        while ($line = $phpcsFile->findNext([T_STRING], $line)) {
            if (strtolower($tokens[$line]['content']) === 'get_class') {
                $phpcsFile->addError(sprintf(self::ERROR_MESSAGE, $tokens[$line]['content']), $line, 'GetClass');
            }

            $line++;
        }
    }

}