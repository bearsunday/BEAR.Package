<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Package;

use Doctrine\Common\Cache\FilesystemCache;
use Ray\Di\DiCompiler;

class CachePageTest extends \PHPUnit_Framework_TestCase
{
    private $tmpDir;

    protected function setUp()
    {
        $this->tmpDir = __DIR__ . '/tmp';
        $unlink = function ($path) use (&$unlink) {
            foreach (glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*') as $file) {
                is_dir($file) ? $unlink($file) : unlink($file);
                @rmdir($file);
            }
        };
        $unlink($this->tmpDir);
        $cmd = 'php ' . __DIR__ . '/blog_posts.php';
        passthru($cmd);
    }

    public function testCachedPage()
    {
        $moduleProvider = function () {return new \Demo\Sandbox\Module\AppModule('prod');};
        $injector = DiCompiler::create($moduleProvider, new FilesystemCache($this->tmpDir), __METHOD__, $this->tmpDir);
        $app = $injector->getInstance('BEAR\Sunday\Extension\Application\AppInterface');
        /** @var $app \BEAR\Package\Provide\Application\AbstractApp */
        $page = $app->resource->get->uri('page://self/blog/posts')->eager->request();
        /** @var $instance \Demo\Sandbox\Resource\Page\Blog\Posts */


        $this->assertInstanceOf('\Demo\Sandbox\Resource\Page\Blog\Posts', $page);
        $html = (string) $page;
        $this->assertContains('<h1>Posts</h1>', $html);
    }
}
