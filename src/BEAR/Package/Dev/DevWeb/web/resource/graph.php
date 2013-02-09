<?php
/**
 * resource graph
 *
 * @global $app \BEAR\Package\Provide\Application\AbstractApp
 */

$resourceObject = $app->resource->newInstance($_GET['uri']);
print_o($resourceObject);
