<?php
/**
 * Global namespace debug function for short cut typing
 *
 * @package BEAR.Package
 */
use BEAR\Package\Debug\Debug;
use BEAR\Package\Debug\Exception\Debug as DebugException;

/**
 * p - debug exception
 *
 */

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
    if (!is_null($var)) {
        Debug::printR(debug_backtrace(), $var, $level);
    }
    throw new DebugException;
}
