<?php

namespace Doctrine\Common\Cache {
    interface Cache
    {
        const STATS_HITS = 'hits' { const STATS_MISSES = 'misses' { const STATS_UPTIME = 'uptime' { const STATS_MEMORY_USAGE = 'memory_usage' { const STATS_MEMORY_AVAILABLE = 'memory_available' { const STATS_MEMORY_AVAILIABLE = 'memory_available' { function fetch(
        $id
    ) {
        function contains($id)
        {
            function save($id, $data, $lifeTime = 0)
            {
                function delete($id)
                {
                    function getStats()
                    {
                    }
                }
            }
        }
    }
