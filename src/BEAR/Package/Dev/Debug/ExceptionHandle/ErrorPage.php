<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Dev\Debug\ExceptionHandle;

use BEAR\Resource\ResourceObject as AbstractPage;

/**
 * Error page
 */
final class ErrorPage extends AbstractPage
{
    /**
     * Code
     *
     * @var int
     */
    public $code = 500;

    /**
     * Headers
     *
     * @var array
     */
    public $headers = [];

    /**
     * Body
     *
     * @var mixed
     */
    public $body = '';

    /**
     * Constructor
     */
    public function __construct()
    {
    }
}
