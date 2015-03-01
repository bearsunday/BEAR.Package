<?php

namespace BEAR\Package\Provide\Transfer;

use Ray\Aop\Arguments;
use Ray\Aop\ReflectiveMethodInvocation;

require_once dirname(dirname(__DIR__)) . '/Fake/Provide/Transfer/header.php';
require_once dirname(dirname(__DIR__)) . '/Fake/Provide/Transfer/http_response_code.php';

class EtagResponseInterceptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    public static $code = [];

    /**
     * @var array
     */
    public static $headers = [];

    public function setup()
    {
        self::$code = [];
        self::$headers = [];
    }
    private function getInvocation($mock, array $server)
    {
        return new ReflectiveMethodInvocation(
            $mock,
            new \ReflectionMethod($mock, 'onGet'),
            new Arguments([$mock, $server]),
            [new EtagResponseInterceptor]
        );
    }

    public function testNoHeader()
    {
        $invocation = $this->getInvocation(new FakeResource, []);
        $invocation->proceed();
        $this->assertSame([], self::$code);
    }

    public function testSameEtag()
    {
        $ro = new FakeResource;
        $server = [];
        $ro->headers['Etag'] = $server['HTTP_IF_NONE_MATCH'] = 'kuma999';
        $ro->headers['Last-Modified'] = 'Wed, 15 Nov 1995 04:58:08 GM';
        $invocation = $this->getInvocation($ro, $server);
        $invocation->proceed();
        $this->assertSame([304], self::$code);
        $this->assertSame([['Cache-Control: public']], self::$headers);
    }

    public function testSameDate()
    {
        $ro = new FakeResource;
        $server = [];
        $ro->headers['Last-Modified'] = $server['HTTP_IF_MODIFIED_SINCE'] = 'Wed, 15 Nov 1995 04:58:08 GM';
        $invocation = $this->getInvocation($ro, $server);
        $invocation->proceed();
        $this->assertSame([304], self::$code);
        $this->assertSame([['Cache-Control: public']], self::$headers);
    }

    public function testNotMatch()
    {
        $ro = new FakeResource;
        $server = [];
        $invocation = $this->getInvocation($ro, $server);
        $invocation->proceed();
        $this->assertSame([], self::$code);
        $this->assertSame([], self::$headers);
    }
}
