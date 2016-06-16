<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http\Test;

use Es\Http\Request;
use Es\Http\Stream;
use Es\Http\Uri;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $body     = new Stream();
        $headers  = ['foo' => ['bar', 'baz']];
        $uri      = new Uri();
        $method   = 'POST';
        $protocol = '1.0';
        $request  = new Request($body, $headers, $uri, $method, $protocol);
        $this->assertSame($body,     $request->getBody());
        $this->assertSame($headers,  $request->getHeaders());
        $this->assertSame($uri,      $request->getUri());
        $this->assertSame($method,   $request->getMethod());
        $this->assertSame($protocol, $request->getProtocolVersion());
    }

    public function testWithRequestTargetReturnsNewInstance()
    {
        $request = new Request();

        $new = $request->withRequestTarget('*');
        $this->assertNotSame($new, $request);
    }

    public function targetDataProvider()
    {
        return [
            ['*', '*'],
            ['http://foo.bar/baz', 'http://foo.bar/baz'],
            ['http://foo.bar/baz?con=com#qoo', 'http://foo.bar/baz?con=com'],
        ];
    }

    /**
     * @dataProvider targetDataProvider
     */
    public function testWithRequestTargetSetTarget($target, $expected)
    {
        $request = new Request();

        $new = $request->withRequestTarget($target);
        $this->assertEquals($expected, $new->getRequestTarget());
    }

    public function testInvalidTargetThrowsException()
    {
        $request = new Request();

        $this->setExpectedException('InvalidArgumentException');
        $request->withRequestTarget(false);
    }

    public function getGetRequestTargetReturnsSpecifiedTarget()
    {
        $request = new Request();

        $new = $request->withRequestTarget('http://example.com/foo');
        $this->assertEquals('http://example.com/foo', $new->getRequestTarget());
    }

    public function testGetRequestTargetReturnsEmptyPathWhenUriNotSpecified()
    {
        $request = new Request();
        $this->assertEquals('/', $request->getRequestTarget());
    }

    public function testGetRequestTargetContainsPathInAnyCase()
    {
        // query without path
        $uri     = new Uri('?foo=bar');
        $request = new Request(null, null, $uri);
        // empty path and query
        $this->assertEquals('/?foo=bar', $request->getRequestTarget());
    }

    public function invalidMethodDataProvider()
    {
        return [
            [true],
            [false],
            [100],
            [[]],
            [new \stdClass()],
        ];
    }

    /**
     * @dataProvider invalidMethodDataProvider
     */
    public function testWithMethodRaiseExceptionIfInvalidMethodProvided($method)
    {
        $request = new Request();
        $this->setExpectedException('InvalidArgumentException');
        $request->withMethod($method);
    }

    public function testWithMethodReturnsNewInstance()
    {
        $request = new Request();

        $new = $request->withMethod('PUT');
        $this->assertNotSame($new, $request);
    }

    public function testWithMethodCaseInsensitiveForResourceCpecificMethods()
    {
        $request = new Request();

        $new = $request->withMethod('FooObjectDoSomething');
        $this->assertEquals('FooObjectDoSomething', $new->getMethod());
    }

    public function restMethodsLowercaseDataProvider()
    {
        $methods = [
            'connect',
            'delete',
            'get',
            'head',
            'options',
            'path',
            'post',
            'put',
            'trace',
        ];
        $return = [];
        foreach ($methods as $method) {
            $return[] = [$method];
        }

        return $return;
    }

    /**
     * @dataProvider restMethodsLowercaseDataProvider
     */
    public function testWithMethodChangesCaseToUpperForRestMethods($method)
    {
        $request = new Request();

        $new = $request->withMethod($method);
        $this->assertEquals(strtoupper($method), $new->getMethod());
    }

    public function invalidUriDataProvider()
    {
        return [
            [true],
            [false],
            [100],
            [[]],
            [new \stdClass()],
        ];
    }

    /**
     * @dataProvider invalidUriDataProvider
     */
    public function testInvalidUriRaiseException($uri)
    {
        $this->setExpectedException('InvalidArgumentException');
        $err = new Request(null, null, $uri);
    }

    public function testWithUriReturnsNewInstance()
    {
        $request = new Request();

        $uri = new Uri();
        $new = $request->withUri($uri);
        $this->assertNotSame($new, $request);
    }

    public function testWithUriSetUriToNewInstance()
    {
        $request = new Request();

        $uri = new Uri();
        $new = $request->withUri($uri);
        $this->assertSame($new->getUri(), $uri);
    }

    public function testWithUriChangeHostHeaderByDefault()
    {
        $request = new Request(null, ['Host' => ['foo.com']]);

        $uri = new Uri('http://bar.org:8080');
        $new = $request->withUri($uri);
        $this->assertEquals(['bar.org:8080'], $new->getHeader('Host'));
    }

    public function testWithUriPreserveHostWhenHostPresents()
    {
        $request = new Request(null, ['Host' => ['foo.com']]);

        $uri = new Uri('http://bar.org');
        $new = $request->withUri($uri, true);
        $this->assertEquals(['foo.com'], $new->getHeader('Host'));
    }

    public function testWithUriParamPreserveHostSetHostWhenHostHeaderNotExists()
    {
        $request = new Request();

        $uri = new Uri('http://bar.org');
        $new = $request->withUri($uri, true);
        $this->assertEquals(['bar.org'], $new->getHeader('Host'));
    }

    public function testWithUriParamPreserveHostDontChangeHostWhenUriHostNotSpecified()
    {
        $request = new Request();

        $uri = new Uri();
        $new = $request->withUri($uri, true);
        $this->assertEquals([], $new->getHeader('Host'));
    }

    public function testGetUriReturnsInstanceOfUriInterface()
    {
        $request = new Request();
        $this->assertInstanceOf('Psr\\Http\\Message\\UriInterface', $request->getUri());
    }
}
