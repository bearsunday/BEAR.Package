<?php

namespace Demo\Sandbox\Resource\Page\Demo\Page\Hyperlink;

use BEAR\Resource\ResourceObject as Page;
use BEAR\Resource\Link;
use BEAR\Sunday\Inject\ResourceInject;
use Ray\Di\Di\Inject;

/**
 * Hyper link to another page
 *
 * @see http://guzzlephp.org/tour/http.html#uri-templates
 */
class Index extends Page
{
    public $links = [
        'help'   => [Link::HREF => 'page://self/demo/page/hyperlink/help'],
        'profile' => [Link::HREF => 'page://self/demo/page/hyperlink/profile{?profile_id}', Link::TEMPLATED => true]
    ];

    /**
     * @param int $user_id
     */
    public function onGet($user_id = 100)
    {
        $this['user_id'] = $user_id;
        $this['profile_id'] = "p{$user_id}";

        return $this;
    }
}
