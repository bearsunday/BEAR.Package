<?php

use Ray\Di\DiCompiler;
use Doctrine\Common\Cache\FilesystemCache;

require dirname(dirname(__DIR__)) . '/tests/bootstrap.php';

$_ENV['TMP_DIR'] = dirname(dirname(__DIR__)) . '/tmp';
echo $_ENV['TMP_DIR'];exit;

$moduleProvider = function () {return new \Demo\Sandbox\Module\AppModule('prod');};
$tmpDir = $_ENV['TMP_DIR'];
$injector = DiCompiler::create($moduleProvider, new FilesystemCache($tmpDir), __METHOD__, $tmpDir);
$app = $injector->getInstance('BEAR\Sunday\Extension\Application\AppInterface');
/** @var $app BEAR\Package\Provide\Application\AbstractApp */
$page = $app->resource->get->uri('page://self/blog/posts')->eager->request();
/** @var $instance \Demo\Sandbox\Resource\Page\Blog\Posts */

return $page;
