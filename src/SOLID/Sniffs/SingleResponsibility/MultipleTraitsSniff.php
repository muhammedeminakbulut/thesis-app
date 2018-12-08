<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\SOLID\Sniffs\SingleResponsibility;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class MultipleTraitsSniff
 *
 * A PHP class with multiple traits has also multiple reasons to change.
 *
 * A class with multiple traits should report an error.
 */
class MultipleTraitsSniff implements Sniff
{
    const ERROR_MESSAGE = 'Single Responsibility principle violation: %s has multiple traits which can be a multiple reason to change.';

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

        $closer = $tokens[$stackPtr]['scope_closer'];
        $useTokens = [];
        for ($i = $stackPtr; $i < $closer;) {
            $i = $phpcsFile->findNext([T_USE], $i + 1, $closer);

            if ($i === false) {
                break;
            }

            $useTokens[] = $i;
        }

        if (count($useTokens) > 1) {
            $phpcsFile->addError(
                sprintf(self::ERROR_MESSAGE, $classNameToken['content']),
                $stackPtr,
                'MultipleTraits'
            );
        }

        foreach ($useTokens as $useToken) {
            $endPointer = $phpcsFile->findNext([T_OPEN_CURLY_BRACKET, T_COMMA, T_SEMICOLON], $useToken);

            if ($tokens[$endPointer]['code'] === T_OPEN_CURLY_BRACKET || $tokens[$endPointer]['code'] === T_COMMA) {
                $phpcsFile->addError(
                    sprintf(self::ERROR_MESSAGE, $classNameToken['content']),
                    $stackPtr,
                    'MultipleTraits'
                );
            }
        }
    }

}