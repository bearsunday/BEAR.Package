<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Dev\Application\Exception;

use RuntimeException;

/**
 * New URI file already exists
 *
 * @package BEAR.Package
 */
class InvalidUri extends RuntimeException implements ExceptionInterface
{
}
