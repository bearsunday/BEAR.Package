<?php
namespace Sandbox\Resource\Page\Auth;

use BEAR\Resource\ResourceObject as Page;
use Sandbox\Annotation\Auth;

/**
 * Authentication sample.
 */
class Index extends Page
{
    /**
     * @Auth
     */
    public function onGet()
    {
        return $this;
    }
}
