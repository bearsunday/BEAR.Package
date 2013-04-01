<?php
/**
 * Global namespace debug function for short cut typing
 *
 * @package BEAR.Package
 */
use BEAR\Package\Dev\Debug\Debug;

/**
 * Debug print
 *
 * @package BEAR.Package
 *
 * @param mixed $var
 * @param int   $level
 */
function p($var = null, $level = 2)
{
    Debug::printR(debug_backtrace(), $var, $level);
}
