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
    const ERROR_MESSAGE = 'Single Responsibility principle violation: %s:%s:%s is an optional parameter which can give the class multiple reasons to change.';

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
        $closer = $tokens[$stackPtr]['scope_closer'];
        $classNameToken = $tokens[$phpcsFile->findNext([T_STRING], $stackPtr)];

        for ($i = $stackPtr; $i < $closer;) {
            $i = $phpcsFile->findNext([T_FUNCTION], $i + 1, $closer);

            if ($i === false) {
                break;
            }

            $methodNameToken = $tokens[$phpcsFile->findNext([T_STRING], $i)];

            /**
             * Ignoring construct because it is an essential part of a class
             */
            if ($methodNameToken['content'] === '__construct') {
                continue;
            }

            $methodProperties = $phpcsFile->getMethodProperties($i);
            $methodParameters = $phpcsFile->getMethodParameters($i);

            if ($methodProperties['scope'] === 'public' || $methodProperties['scope_specified'] === false) {
                foreach ($methodParameters as $parameter) {
                    if ($parameter['nullable_type']) {
                        $phpcsFile->addError(
                            sprintf(
                                self::ERROR_MESSAGE,
                                $classNameToken['content'],
                                $methodNameToken['content'],
                                $parameter['name']
                            ),
                            $stackPtr,
                            'NullableType'
                        );
                    }

                    if ($parameter['default']) {
                        $phpcsFile->addError(
                            sprintf(
                                self::ERROR_MESSAGE,
                                $classNameToken['content'],
                                $methodNameToken['content'],
                                $parameter['name']
                            ),
                            $stackPtr,
                            'DefaultParameter'
                        );
                    }
                }
            }
        }
    }
}