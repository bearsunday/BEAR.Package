<?php
/**
 * Application instance script
 *
 * @return $app  \BEAR\Sunday\Extension\Application\AppInterface
 * @global $mode string configuration mode
 */
namespace Sandbox;

use BEAR\Package\Provide\Application\ApplicationFactory;

require_once __DIR__ . '/bootstrap.php';

// mode
$mode = isset($mode) ? $mode : 'Prod';

// new application instance
$app = (new ApplicationFactory)->newInstance(__NAMESPACE__, $mode);

return $app;
