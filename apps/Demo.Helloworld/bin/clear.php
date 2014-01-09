<?php
/**
 * Application clear script
 */
if (function_exists('apc_clear_cache')) {
    if (version_compare(phpversion('apc'), '4.0.0') < 0) {
        apc_clear_cache('user');
    }
    apc_clear_cache();
}
