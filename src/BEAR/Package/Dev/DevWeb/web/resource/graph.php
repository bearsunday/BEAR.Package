<?php

$resourceObject = $app->resource->newInstance($_GET['uri']);
print_o($resourceObject);
