<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\SOLID\Sniffs\DependencyInversion;

/**
 * Class DependenciesAbstractOrInterfacesSniff
 *
 * If dependencies of classes are concrete classes so not a Interface or Abstract class this is a violation of the
 * Dependency Inversion Principle. This will result in an error.
 *
 * public function __construct(HttpClient $client) is an error
 * public function __construct(HttpClientInterface $client) is not an error.
 */
class DependenciesAbstractOrInterfacesSniff
{

}