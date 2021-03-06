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
    const ERROR_MESSAGE = 'Single Responsibility principle violation: %s has multiple public functions. All public functions are a reason to change this class.';

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_CLASS,
        ];
    }

    /**
     * @inheritdoc
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $closer = $tokens[$stackPtr]['scope_closer'];
        $count = 0;
        for ($i = $stackPtr; $i < $closer;) {
            $i = $phpcsFile->findNext([T_FUNCTION], $i + 1, $closer);

            if ($i === false) {
                break;
            }

            $methodProperties = $phpcsFile->getMethodProperties($i);

            $methodNameToken = $tokens[$phpcsFile->findNext([T_STRING], $i)];

            /**
             * Ignoring construct because it is an essential part of a class
             */
            if ($methodNameToken['content'] === '__construct') {
                continue;
            }

            if ($methodProperties['scope'] === 'public' || $methodProperties['scope_specified'] === false) {
                $count++;
            }
        }

        if ($count > 1) {
            $classNameToken = $tokens[$phpcsFile->findNext([T_STRING], $stackPtr)];
            $phpcsFile->addError(
                sprintf(self::ERROR_MESSAGE, $classNameToken['content']),
                $stackPtr,
                'MultiplePublicFunctions'
            );
        }
    }

}