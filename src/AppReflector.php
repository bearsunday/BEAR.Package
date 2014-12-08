<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package;

use Koriym\Psr4List\Psr4List;

class AppReflector
{
    /**
     * @var AbstractAppMeta
     */
    private $appMeta;

    /**
     * @param AbstractAppMeta $appMeta
     */
    public function __construct(AbstractAppMeta $appMeta)
    {
        $this->appMeta = $appMeta;
    }

    /**
     * @return \Generator
     */
    public function resourceList()
    {
        $list = new Psr4List;
        $resourceListGenerator =  $list($this->appMeta->name . '\Resource', $this->appMeta->appDir . '/src/Resource');

        return $resourceListGenerator;
    }
}
