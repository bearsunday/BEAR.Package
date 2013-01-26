<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Debug\ExceptionHandle;

use BEAR\Resource\AbstractObject as AbstractPage;
/**
 * Error page
 *
 * @package    BEAR.Sunday
 * @subpackage Page
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
