<?php

namespace BEAR\Package\Mock\ResourceObject;

use BEAR\Resource\ResourceObject;

/**
 * Ok page
 *
 * @package    BEAR.Sunday
 * @subpackage Page
 */
final class Ok extends ResourceObject
{
    /**
     * Code
     *
     * @var int
     */
    public $code = 200;

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

    /**
     * Get
     *
     * @return $this
     */
    public function onGet()
    {
        return $this;
    }
}
