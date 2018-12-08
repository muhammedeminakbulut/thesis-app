<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\SOLID\Sniffs\OpenClosed;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class FinalSniff
 *
 * A class which is not Final will result in an error because it is not Closed.
 */
class FinalSniff implements Sniff
{
    const ERROR_MESSAGE = 'Open Closed principle violation: %s class is not `Closed` because it is not declared final and it is not abstract.';

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

        $properties = $phpcsFile->getClassProperties($stackPtr);

        if ($properties['is_abstract'] === false && $properties['is_final'] === false) {
            $phpcsFile->addError(
                sprintf(self::ERROR_MESSAGE, $classNameToken['content']),
                $stackPtr,
                'NotClosed'
            );
        }
    }

}