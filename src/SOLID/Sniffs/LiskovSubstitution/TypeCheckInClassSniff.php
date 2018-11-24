<?php
/**
 * Copyright (c) Muhammed Akbulut
 */

namespace App\SOLID\Sniffs\LiskovSubstitution;

/**
 * Class TypeCheckInClassSniff
 *
 * A class in its functions having type checks about which class it is dealing with is breaking the liskov substitution
 * principle thus a type check `instanceOf` or `get_class` will result in an error.
 */
class TypeCheckInClassSniff
{

}