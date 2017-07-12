<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
$context = PHP_SAPI === 'cli' ? 'cli-hal-api-app' : 'prod-hal-api-app';
require __DIR__ . '/bootstrap.php';
