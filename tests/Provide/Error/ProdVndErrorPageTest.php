<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Router\RouterMatch;
use PHPUnit\Framework\TestCase;

class ProdVndErrorPageTest extends TestCase
{
    /**
     * @var ProdVndErrorPage
     */
    private $page;

    protected function setUp() : void
    {
        parent::setUp();
        $e = new \LogicException('bear');
        $request = new RouterMatch();
        list($request->method, $request->path, $request->query) = ['get', '/', []];
        $this->page = (new ProdVndErrorPageFactory())->newInstance($e, $request);
    }

    public function testToString()
    {
        $this->page->toString();
        $this->assertSame(500, $this->page->code);
        $this->assertArrayHasKey('content-type', $this->page->headers);
        $this->assertSame('application/vnd.error+json', $this->page->headers['content-type']);
        $this->assertSame('{
    "message": "Internal Server Error",
    "logref": "{logref}"
}
', $this->page->view);
    }
}
