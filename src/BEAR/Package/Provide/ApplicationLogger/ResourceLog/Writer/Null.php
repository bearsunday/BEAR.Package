<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

use BEAR\Resource\LogWriterInterface;
use BEAR\Resource\RequestInterface;
use BEAR\Resource\AbstractObject as ResourceObject;

final class Null implements LogWriterInterface
{
    /**
     * @var array
     */
    public $logs = [];

    public function write(RequestInterface $request, ResourceObject $result)
    {
        $this->logs[] = [$request, $result];
    }
}
