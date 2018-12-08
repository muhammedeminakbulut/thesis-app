<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\SOLID\Sniffs\OpenClosed;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class InterfacesOrAbstractSniff
 *
 * A class with Interfaces or itself would be abstract is an indication it is Open.
 * A class which is not Open is an violation of the OpenClosed Principle.
 * A class missing Interfaces and which is not Abstract would result in an error.
 */
class InterfacesOrAbstractSniff implements Sniff
{
    const ERROR_MESSAGE = 'Open Closed principle violation: %s class is not `Open` because it does not implement interfaces or is not abstract.';

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

        if ($properties['is_abstract'] === false && $phpcsFile->findImplementedInterfaceNames($stackPtr) === false) {
            $phpcsFile->addError(
                sprintf(self::ERROR_MESSAGE, $classNameToken['content']),
                $stackPtr,
                'NotOpen'
            );
        }
    }

}