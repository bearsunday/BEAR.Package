<?php
/**
 * Global namespace debug function for short cut typing
 *
 * @package BEAR.Package
 */

/**
 * p - debug exception
 *
 */
use BEAR\Package\Debug\Debug;
use BEAR\Package\Debug\Exception\Debug as DebugException;

/**
 * Throw exception
 *
 * @param null $var
 * @param int  $level
 *
 * @package BEAR.Package
 * @throws BEAR\Package\Debug\Exception\Debug
 */
function e($var = null, $level = 2)
{
    if (! is_null($var)) {
        Debug::printR(debug_backtrace(), $var, $level);
    }
    throw new DebugException;
}
