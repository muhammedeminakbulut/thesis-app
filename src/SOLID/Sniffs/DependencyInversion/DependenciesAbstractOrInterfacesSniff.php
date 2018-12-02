<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\SOLID\Sniffs\DependencyInversion;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Class DependenciesAbstractOrInterfacesSniff
 *
 * If dependencies of classes are concrete classes so not a Interface or Abstract class this is a violation of the
 * Dependency Inversion Principle. This will result in an error.
 *
 * public function __construct(HttpClient $client) is an error
 * public function __construct(HttpClientInterface $client) is not an error.
 */
class DependenciesAbstractOrInterfacesSniff implements Sniff
{
    const ERROR_MESSAGE = 'Dependency Inversion Principle violation: %s is not an interface nor abstract contract.';
    const SEARCH_ABSTRACT = 'abstract';
    const SEARCH_INTERFACE = 'interface';

    const IGNORE_TYPE_HINTS = [
        'object',
        'string',
        'iterable',
        'int',
        'float',
        'bool',
        'callable',
        'array',
    ];

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

        $line = $stackPtr;
        while ($line = $phpcsFile->findNext([T_STRING], $line)) {
            if ($tokens[$line]['content'] === '__construct') {
                break;
            }
            $line++;
        }

        $properties = $phpcsFile->getMethodParameters($phpcsFile->findPrevious([T_FUNCTION], $line));

        foreach ($properties as $property) {
            if (in_array($property['type_hint'], self::IGNORE_TYPE_HINTS)) {
                continue;
            }

            if (strpos(strtolower($property['type_hint']), self::SEARCH_ABSTRACT) === false
                && strpos(strtolower($property['type_hint']), self::SEARCH_INTERFACE) === false) {
                $phpcsFile->addError(
                    sprintf(self::ERROR_MESSAGE, $property['type_hint']),
                    $property['type_hint_token'],
                    'DependencyNonAbstractOrInterface'
                );
            }
        }
    }
}