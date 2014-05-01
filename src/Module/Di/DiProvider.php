<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Di;

use BEAR\Sunday\Inject\TmpDirInject;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPParser_PrettyPrinter_Default;
use Ray\Aop\Bind;
use Ray\Aop\Compiler;
use Ray\Di\AbstractModule;
use Ray\Di\Annotation;
use Ray\Di\Config;
use Ray\Di\Container;
use Ray\Di\Definition;
use Ray\Di\Forge;
use Ray\Di\Injector;
use Ray\Di\ProviderInterface;
use Ray\Di\Logger;

class DiProvider implements ProviderInterface
{
    use TmpDirInject;

    private $module;

    public function __construct(AbstractModule $module)
    {
        $this->module = $module;
    }

    public function get()
    {
        $injector = new Injector(
            new Container(
                new Forge(
                    new Config(
                        new Annotation(
                            new Definition,
                            new AnnotationReader
                        )
                    )
                )
            ),
            $this->module,
            new Bind,
            new Compiler(
                $this->tmpDir,
                new PHPParser_PrettyPrinter_Default
            ),
            new Logger
        );
        return $injector;
    }
}
