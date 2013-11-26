<?php

logic: {
    $versions = [
            'php' => phpversion(),
            'apc' => extension_loaded('apc') ? phpversion('apc') : 'n/a',
            'curl' => extension_loaded('curl') ? 'yes' : 'n/a',
            'memcache' => extension_loaded('memcache') ? phpversion('memcache') : 'n/a',
            'mysqlnd' => extension_loaded('mysqlnd') ? phpversion('mysqlnd') : 'n/a',
            'pdo_sqlite' => extension_loaded('pdo_sqlite') ? phpversion('pdo_sqlite') : 'n/a',
            'xdebug' => extension_loaded('Xdebug') ? phpversion('Xdebug') : 'n/a',
            'xhprof' => extension_loaded('xhprof') ? phpversion('xhprof') : 'n/a'
    ];
    $versionView= '';
    foreach ($versions as $ext => $version) {
        $versionView .= "<li><b>{$ext}</b>: {$version}</li>";
    }
}

view: {
    $contentsForLayout =<<<EOT

    <div class="well">
        <ul>
            <li><a href="apc/apc.php"><i class="icon-zoom-in"></i> apc</a></li>
            <li><a href="memcache/"><i class="icon-zoom-in"></i> memcache</a></li>
            <li><a href="xhprof_html/"><i class="icon-zoom-in"></i> xhprof</a></li>
            <li><a href="db.php"><i class="icon-zoom-in"></i> adminer</a></li>
            <li><a href="phpinfo/"><i class="icon-zoom-in"></i> phpinfo</a></li>
        </ul>
    </div>

    <ul class="breadcrumb">
    <li class="active">Versions / Extensions</li>
    </ul>

    <div class="well">
        <ul>
            {$versionView}
        </ul>
    </div>
EOT;
    echo include __DIR__ . '/layout.php';
}

