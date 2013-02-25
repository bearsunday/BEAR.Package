<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

use BEAR\Resource\RequestInterface;
use BEAR\Resource\AbstractObject as ResourceObject;

/**
 * Resource log write interface
 */
interface WriterInterface
{
    /**
     * {@inheritDoc}
     */
    public function write(RequestInterface $request, ResourceObject $result);
}
