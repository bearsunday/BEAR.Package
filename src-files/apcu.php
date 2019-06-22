<?php

declare(strict_types=1);

if (! function_exists('apcu_store')) {
    function apcu_cache_info($limited = false)
    {
    }

    function apcu_clear_cache()
    {
    }

    function apcu_sma_info($limited = false)
    {
    }

    function apcu_store($key, $var, $ttl = 0)
    {
    }

    function apcu_fetch($key, &$success = null)
    {
    }

    function apcu_delete($key)
    {
    }

    function apcu_add($key, $var, $ttl = 0)
    {
    }

    function apcu_exists($keys)
    {
    }

    function apcu_inc($key, $step = 1, &$success = null)
    {
    }

    function apcu_dec($key, $step = 1, &$success = null)
    {
    }

    function apcu_cas($key, $old, $new)
    {
    }

    function apcu_entry($key, callable $generator, $ttl = 0)
    {
    }
}
