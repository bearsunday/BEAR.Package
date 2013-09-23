<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger\ResourceLog\Writer;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\LogWriterInterface;
use BEAR\Resource\RequestInterface;

/**
 * Null logger
 */
final class Null implements LogWriterInterface
{
    /**
     * @var array
     */
    public $logs = [];

    /**
     *  {@inheritDoc}
     */
    public function write(RequestInterface $request, ResourceObject $result)
    {
        $this->logs[] = [$request, $result];
    }
}
