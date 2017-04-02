<?php

namespace Doctrine\Common\Cache;

function apc_fetch($key, &$success = true)
{
    return apcu_fetch($key, $success);
}

function apc_store($key, $var, $ttl = 0)
{
    return apcu_store($key, $var, $ttl);
}
