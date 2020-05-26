<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Resource\ResourceObject;

/**
 * @deprecated
 */
class ErrorPage extends ResourceObject
{
    /**
     * @var string
     */
    private $postBody;

    public function __construct(string $postBody)
    {
        $this->postBody = $postBody;
    }

    public function __toString()
    {
        $string = parent::__toString();

        return $string . $this->postBody;
    }
}
