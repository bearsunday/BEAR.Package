
<?php
/**
 * @global $appDir
 */
// clear APC cache


$view['app_name'] = $appDir;
$contentsForLayout =<<<EOT
    <ul class="breadcrumb">
    <li class="active">Home</li>
    </ul>

    <div class="well">
        <ul>
            <li><a href="resource/"><i class="icon-th-list"></i> Resource</a></li>
            <li><a href="log/"><i class="icon-th-list"></i> Log</a></li>
            <li><a href="refresh/"><i class="icon-refresh"></i> Refresh</a></li>
            <li><a href="di/"><i class="icon-info-sign"></i> Di Log</a></li>
        </ul>
        <hr>
        <ul>
            <li><a href="app/"><i class="icon-zoom-in"></i> Application graph</a></li>
            <li><a href="ide/"><i class="icon-th-list"></i> IDE</a></li>
        </ul>
        <hr>
        <ul>
            <li><a href="apc/"><i class="icon-zoom-in"></i> APC admin</a></li>
            <li><a href="memcache/"><i class="icon-zoom-in"></i> Memcache admin</a></li>
            <li><a href="phpinfo/"><i class="icon-info-sign"></i> phpinfo</a></li>
        </ul>
        <hr>
            <a href="/"><i class="icon-arrow-left"></i> Back</a>
    </div>
EOT;
echo include __DIR__ . '/view/layout.php';
