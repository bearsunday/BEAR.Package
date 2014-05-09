<?php
/**
 * resource graph
 *
 * @global $app \BEAR\Package\Provide\Application\AbstractApp
 */

use Koriym\Printo\Printo;

$resourceObject = $app->resource->newInstance($_GET['uri']);
echo (new Printo($resourceObject));
