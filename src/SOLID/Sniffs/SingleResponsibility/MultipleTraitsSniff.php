<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

/**
 * Created by PhpStorm.
 * User: muhammed
 * Date: 2018-11-24
 * Time: 20:16
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