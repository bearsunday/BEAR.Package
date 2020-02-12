<?php

declare(strict_types=1);

namespace MyVendor\MyProjecct\Resource\Page;

use BEAR\Package\Bootstrap;
use BEAR\Resource\ResourceInterface;
use MyVendor\MyProject\Resource\Page\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();
        $this->resource = (new Bootstrap)->getApp('MyVendor\MyProject', 'app')->resource;
    }

    public function testGet()
    {
        $ro = $this->resource->uri('page://self/user')(['id' => 1]);
        $this->assertSame(200, $ro->code);
        /** @var User $user */
        $user = $ro->body['user'];
        $this->assertSame('page://self/api/user?id=1', $user->toUri());
        $expectRo = '{
    "user": {
        "website": {
            "url": "http:://example.org/1"
        },
        "contact": {
            "contact": [
                {
                    "id": "1",
                    "name": "Athos"
                },
                {
                    "id": "2",
                    "name": "Porthos"
                },
                {
                    "id": "3",
                    "name": "Aramis"
                }
            ]
        },
        "id": "1",
        "name": "Koriym"
    }
}
';
        $f = (string) $ro;
        $this->assertSame($expectRo, (string) $ro);
    }
}
