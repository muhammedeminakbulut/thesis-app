<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\SOLID\Sniffs\InterfaceSegregation;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class MultipleContractsSniff
 *
 * A class with multiple contracts on one interface violates the interface segregation principle.
 *
 * This sniff registers an error when a class has multiple public functions.
 */
class MultipleContractsSniff implements Sniff
{
    const ERROR_MESSAGE = 'Interface Segregation principle violation: %s has multiple contracts on one interface.';

    /**
     * @inheritdoc
     */
    public function register()
    {
        return [
            T_INTERFACE,
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
                'MultipleContracts'
            );
        }
    }

}