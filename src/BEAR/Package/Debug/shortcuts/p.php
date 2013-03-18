<?php
/**
 * Global namespace debug function for short cut typing
 *
 * @package BEAR.Package
 */
use BEAR\Package\Debug\Debug;

/**
 * p - debug print function
 *
 */

/**
 * Debug print
 *
 * @package BEAR.Package
 *
 * @param mixed $var
 * @param int   $level
 */
function p($var, $level = 2)
{
    Debug::printR(debug_backtrace(), $var, $level);
}
