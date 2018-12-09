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
    const ERROR_MESSAGE = 'Dependency Inversion principle violation: %s is not an interface nor abstract contract.';
    const SEARCH_ABSTRACT = 'abstract';
    const SEARCH_INTERFACE = 'interface';

    const IGNORE_TYPE_HINTS = [
        '',
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

        $constructorToken = null;

        $closer = $tokens[$stackPtr]['scope_closer'];

        $functions = [];
        for ($i = $stackPtr; $i < $closer;) {
            $i = $phpcsFile->findNext([T_FUNCTION], $i + 1, $closer);

            if ($i === false) {
                break;
            }

            $functions[] = $i;
        }

        foreach ($functions as $function) {
            $methodProperties = $phpcsFile->getMethodProperties($function);
            if ($methodProperties['scope'] !== 'public') {
                continue;
            }

            $parameters = $phpcsFile->getMethodParameters($function);

            foreach ($parameters as $parameter) {
                if (in_array($parameter['type_hint'], self::IGNORE_TYPE_HINTS)) {
                    continue;
                }

                if (strpos(strtolower($parameter['type_hint']), self::SEARCH_ABSTRACT) === false
                    && strpos(strtolower($parameter['type_hint']), self::SEARCH_INTERFACE) === false) {
                    $phpcsFile->addError(
                        sprintf(self::ERROR_MESSAGE, $parameter['type_hint']),
                        $parameter['type_hint_token'],
                        'DependencyNonAbstractOrInterface'
                    );
                }
            }
        }
    }
}