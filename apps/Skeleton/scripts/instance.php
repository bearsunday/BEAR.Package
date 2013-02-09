<?php
/**
 * Application instance script
 *
 * @return $app  \BEAR\Sunday\Extension\Application\AppInterface
 * @global $mode string configuration mode
 */
namespace Skeleton;

use BEAR\Package\Provide\Application\ApplicationFactory;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\FilesystemCache;

// Profile
// require dirname(dirname(dirname(__DIR__))) . '/scripts/profile.php';

require_once __DIR__ . '/bootstrap.php';

// mode
$mode = isset($mode) ? $mode : 'Prod';

// new application instance
$cache = function_exists('apc_fetch') ? new ApcCache : new FilesystemCache(dirname(__DIR__) . '/data/tmp/cache');
$app = (new ApplicationFactory($cache))->newInstance(__NAMESPACE__, $mode);

return $app;
