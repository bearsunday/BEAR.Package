<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ResourceView;

use BEAR\Resource\ResourceObject;

interface HalFactoryInterface
{
    /**
     * Return Hal object
     *
     * @param ResourceObject $ro
     * @param                $data
     *
     * @return \Nocarrier\Hal
     */
    public function get(ResourceObject $ro, $data);
}
