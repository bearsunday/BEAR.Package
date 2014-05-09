
<?php
/**
 * @global $appDir
 */

$view['app_name'] = $appDir;
$contentsForLayout =<<<EOT
    <style>
        .glyphicon {
            font-size: 20px;
            color: black;
        }
        ul {
            list-style: none;
            line-height: 50px;
        }
    </style>
    <ul class="breadcrumb">
    <li class="active">Home</li>
    </ul>
    <div class="">
        <span class="label label-success">GET</span>
        <ul class="">
            <li><a href="/dev/log/"><span class="glyphicon glyphicon-list"></span> Resource Log</a></li>
            <li><a href="/dev/resource/"><span class="glyphicon glyphicon-list"></span> </i> Resource List</a></li>
            <li><a href="/dev/di/"><span class="glyphicon glyphicon-list"></span> Di Log</a></li>
            <li><a href="/dev/app/"><span class="glyphicon glyphicon-list"></span> Application Object Graph</a></li>
        </ul>
        <hr>
        <span class="label label-danger">POST</span>
        <ul>
            <li><a href="/dev/resource/new""><span class="glyphicon glyphicon-plus"></span> Add Resource</a></li>
        </ul>
        <hr>
        <span class="label label-warning">PUT</span>
        <ul>
            <li><a href="/dev/ide/"><span class="glyphicon glyphicon-edit"></span> Edit Application</a></li>
        </ul>
        <hr>
        <span class="label label-warning">DELETE</span>
        <ul>
            <li><a href="/dev/clear/"><span class="glyphicon glyphicon-trash"></span></i> Clear Cache</a></li>
        </ul>
    </div>
    ****
EOT;
echo include __DIR__ . '/view/layout.php';
