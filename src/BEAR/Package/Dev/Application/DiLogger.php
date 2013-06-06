<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Dev\Application;

use Aura\Di\Exception;
use BEAR\Package\Dev\Application\Exception\FileAlreadyExists;
use BEAR\Package\Dev\Application\Exception\InvalidUri;
use BEAR\Package\Dev\Application\Exception\NotWritable;
use BEAR\Resource\AbstractObject as ResourceObject;
use BEAR\Resource\Exception\ResourceNotFound;
use BEAR\Sunday\Extension\Application\AppInterface;
use Ray\Di\Exception\NotInstantiable;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use BEAR\Resource\Exception\Uri;

/**
 * Application reflector
 */
class DiLogger
{
    public function __construct()
}
