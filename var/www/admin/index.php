<?php

$contentsForLayout =<<<EOT
    <ul class="breadcrumb">
    <li class="active">Admin</li>
    </ul>

    <div class="well">
        <ul>
            <li><a href="apc/apc.php"><i class="icon-zoom-in"></i> APC</a></li>
            <li><a href="memcache/"><i class="icon-zoom-in"></i> Memcache</a></li>
            <li><a href="phpinfo/"><i class="icon-zoom-in"></i> phpinfo()</a></li>
        </ul>
    </div>
EOT;

echo include __DIR__ . '/layout.php';