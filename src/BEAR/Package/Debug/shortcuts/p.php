<?php
/**
 * Global namespace debug function for short cut typing
 *
 * @package BEAR.Package
 */

/**
 * p - debug print function
 *
 */
use BEAR\Package\Debug\Debug;

/**
 * Debug print
 *
 * @package BEAR.Package
 *
 * @param $var
 */
function p($var)
{
    Debug::printR(debug_backtrace(), $var);
}