<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Error;

use BEAR\Resource\ResourceObject;

class ErrorPage extends ResourceObject
{
    /**
     * @var string
     */
    private $postBody;

    public function __construct($postBody)
    {
        $this->postBody = $postBody;
    }

    public function __toString()
    {
        $string = parent::__toString();

        return $string . $this->postBody;
    }
}
