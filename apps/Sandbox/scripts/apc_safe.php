<?php
/**
 * Apc dummy function
 *
 */
if (! function_exists('apc_clear_cache')) {
    function apc_clear_cache(){};
    function apc_fetch($key, &$success = false){return false;}
    function apc_store($key, $var, $ttl=null){}
    function apc_delete($key){}
}