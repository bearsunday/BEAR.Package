<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Package\Exception\InvalidRequestJsonException;
use PHPUnit\Framework\TestCase;

class HttpMethodParamsTest extends TestCase
{
    public function testGet()
    {
        $server = ['REQUEST_METHOD' => 'GET'];
        $get = ['id' => '1'];
        $post = [];
        list($method, $params) = (new HttpMethodParams)->get($server, $get, $post);
        $this->assertSame('get', $method);
        $this->assertSame(['id' => '1'], $params);
    }

    public function testPost()
    {
        $server = ['REQUEST_METHOD' => 'POST'];
        $get = [];
        $post = ['id' => '1'];
        list($method, $params) = (new HttpMethodParams)->get($server, $get, $post);
        $this->assertSame('post', $method);
        $this->assertSame(['id' => '1'], $params);
    }

    public function testPut()
    {
        $server = ['REQUEST_METHOD' => 'PUT', HttpMethodParams::CONTENT_TYPE => HttpMethodParams::FORM_URL_ENCODE];
        $get = ['name' => 'bear'];
        $post = ['name' => 'sunday'];
        $httpMethodParam = new HttpMethodParams;
        $httpMethodParam->setStdIn(__DIR__ . '/query.txt');
        list($method, $params) = $httpMethodParam->get($server, $get, $post);
        $this->assertSame('put', $method);
        $this->assertSame(['name' => 'kuma'], $params);
    }

    public function testPatch()
    {
        $server = ['REQUEST_METHOD' => 'PATCH', HttpMethodParams::CONTENT_TYPE => HttpMethodParams::FORM_URL_ENCODE];
        $get = ['name' => 'bear'];
        $post = ['name' => 'sunday'];
        $httpMethodParam = new HttpMethodParams;
        $httpMethodParam->setStdIn(__DIR__ . '/query.txt');
        list($method, $params) = $httpMethodParam->get($server, $get, $post);
        $this->assertSame(['name' => 'kuma'], $params);
    }

    public function testDelete()
    {
        $server = ['REQUEST_METHOD' => 'DELETE', HttpMethodParams::CONTENT_TYPE => HttpMethodParams::FORM_URL_ENCODE];
        $get = ['name' => 'bear'];
        $post = ['name' => 'sunday'];
        $httpMethodParam = new HttpMethodParams;
        $httpMethodParam->setStdIn(__DIR__ . '/query.txt');
        list($method, $params) = $httpMethodParam->get($server, $get, $post);
        $this->assertSame('delete', $method);
        $this->assertSame(['name' => 'kuma'], $params);
    }

    public function testOverridePut()
    {
        $server = ['REQUEST_METHOD' => 'POST', HttpMethodParams::CONTENT_TYPE => HttpMethodParams::FORM_URL_ENCODE];
        $post = ['_method' => 'PUT', 'id' => 1];
        list($method, $param) = (new HttpMethodParams)->get($server, [], $post);
        $this->assertSame('put', $method);
        $expected = ['id' => 1];
        $this->assertSame($expected, $param);
    }

    public function testOverridePatch()
    {
        $server = ['REQUEST_METHOD' => 'POST', HttpMethodParams::CONTENT_TYPE => HttpMethodParams::FORM_URL_ENCODE];
        $post = ['_method' => 'PATCH', 'id' => 1];
        list($method, $param) = (new HttpMethodParams)->get($server, [], $post);
        $this->assertSame('patch', $method);
        $expected = ['id' => 1];
        $this->assertSame($expected, $param);
    }

    public function testOverrideDelete()
    {
        $server = ['REQUEST_METHOD' => 'POST', HttpMethodParams::CONTENT_TYPE => HttpMethodParams::FORM_URL_ENCODE];
        $post = ['_method' => 'DELETE', 'id' => 1];
        list($method, $param) = (new HttpMethodParams)->get($server, [], $post);
        $this->assertSame('delete', $method);
        $expected = ['id' => 1];
        $this->assertSame($expected, $param);
    }

    public function testOverrideHeaderPut()
    {
        $server = ['REQUEST_METHOD' => 'POST', 'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PUT'];
        $post = ['name' => 'sunday'];
        list($method) = (new HttpMethodParams)->get($server, [], $post);
        $this->assertSame('put', $method);
    }

    public function testOverrideHeaderPatch()
    {
        $server = ['REQUEST_METHOD' => 'POST', 'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PATCH'];
        $post = ['name' => 'sunday'];
        list($method) = (new HttpMethodParams)->get($server, [], $post);
        $this->assertSame('patch', $method);
    }

    public function testOverrideHeaderDelete()
    {
        $server = ['REQUEST_METHOD' => 'POST', 'HTTP_X_HTTP_METHOD_OVERRIDE' => 'DELETE'];
        $post = ['name' => 'sunday'];
        list($method) = (new HttpMethodParams)->get($server, [], $post);
        $this->assertSame('delete', $method);
    }

    public function testPostContentTypeJson()
    {
        $httpMethodParam = new HttpMethodParams;
        $httpMethodParam->setStdIn(__DIR__ . '/json.txt');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTP_CONTENT_TYPE' => 'application/json'
        ];
        list(, $params) = $httpMethodParam->get($server, [], []);
        $expected = ['name' => 'BEAR.Sunday v1.0', 'age' => 0];
        $this->assertSame($expected, $params);
    }

    public function testPostContentTypeJsonAssocArray()
    {
        $httpMethodParam = new HttpMethodParams;
        $httpMethodParam->setStdIn(__DIR__ . '/json_assoc.txt');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTP_CONTENT_TYPE' => 'application/json'
        ];
        list(, $params) = $httpMethodParam->get($server, [], []);
        $expected = ['franeworks' => [['name' => 'BEAR.Sunday v1.0', 'age' => 0], ['name' => 'zend', 'age' => 9]]];
        $this->assertSame($expected, $params);
    }

    public function testPostContentTypeJsonEmpty()
    {
        $this->expectException(InvalidRequestJsonException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Syntax error');
        $httpMethodParam = new HttpMethodParams;
        $httpMethodParam->setStdIn(__DIR__ . '/json_empty.txt');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTP_CONTENT_TYPE' => 'application/json'
        ];
        list(, $params) = $httpMethodParam->get($server, [], []);
        $expected = null;
        $this->assertSame($expected, $params);
    }

    public function testPostContentTypeJsonInvalid()
    {
        $this->expectException(InvalidRequestJsonException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Syntax error');
        $httpMethodParam = new HttpMethodParams;
        $httpMethodParam->setStdIn(__DIR__ . '/json_invalid.txt');
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTP_CONTENT_TYPE' => 'application/json'
        ];
        list(, $params) = $httpMethodParam->get($server, [], []);
        $expected = null;
        $this->assertSame($expected, $params);
    }

    public function testPostContentTypeUnknown()
    {
        $httpMethodParam = new HttpMethodParams;
        $server = [
            'REQUEST_METHOD' => 'POST',
            'HTTP_CONTENT_TYPE' => 'text/xml'
        ];
        list(, $params) = $httpMethodParam->get($server, [], []);
        $expected = [];
        $this->assertSame($expected, $params);
    }

    public function testPostNoContentType()
    {
        $httpMethodParam = new HttpMethodParams;
        $server = [
            'REQUEST_METHOD' => 'POST',
        ];
        list(, $params) = $httpMethodParam->get($server, [], []);
        $expected = [];
        $this->assertSame($expected, $params);
    }

    public function testPutContentTypeJson()
    {
        $httpMethodParam = new HttpMethodParams;
        $httpMethodParam->setStdIn(__DIR__ . '/json.txt');
        $server = [
            'REQUEST_METHOD' => 'PUT',
            'HTTP_CONTENT_TYPE' => 'application/json'
        ];
        list(, $params) = $httpMethodParam->get($server, [], []);
        $expected = ['name' => 'BEAR.Sunday v1.0', 'age' => 0];
        $this->assertSame($expected, $params);
    }

    public function testPutContentTypeJsonAssocArray()
    {
        $httpMethodParam = new HttpMethodParams;
        $httpMethodParam->setStdIn(__DIR__ . '/json_assoc.txt');
        $server = [
            'REQUEST_METHOD' => 'PUT',
            'HTTP_CONTENT_TYPE' => 'application/json'
        ];
        list(, $params) = $httpMethodParam->get($server, [], []);
        $expected = ['franeworks' => [['name' => 'BEAR.Sunday v1.0', 'age' => 0], ['name' => 'zend', 'age' => 9]]];
        $this->assertSame($expected, $params);
    }

    public function testPutContentTypeUnknown()
    {
        $httpMethodParam = new HttpMethodParams;
        $server = [
            'REQUEST_METHOD' => 'PUT',
            'HTTP_CONTENT_TYPE' => 'text/xml'
        ];
        list(, $params) = $httpMethodParam->get($server, [], []);
        $expected = [];
        $this->assertSame($expected, $params);
    }

    public function testPutNoContentType()
    {
        $httpMethodParam = new HttpMethodParams;
        $server = [
            'REQUEST_METHOD' => 'PUT',
        ];
        list(, $params) = $httpMethodParam->get($server, [], []);
        $expected = [];
        $this->assertSame($expected, $params);
    }
}
