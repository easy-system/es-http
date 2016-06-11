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

use Es\Http\Factory\UploadedFilesFactory;
use Es\Http\ServerRequest;
use Es\Http\Stream;
use Es\Http\UploadedFile;
use Es\Http\Uri;

class ServerRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $serverParams  = ['foo' => 'bar'];
        $cookieParams  = ['ban' => 'bar'];
        $queryParams   = ['bar' => 'bat'];
        $uploadedFiles = ['bat' => new UploadedFile('foo', 'ban', 'bar', 100, 0)];
        $attributes    = ['baz' => 'con'];
        $parsedBody    = ['con' => 'cor'];
        $body          = new Stream();
        $headers       = ['cop' => ['cot', 'coz']];
        $uri           = new Uri();
        $method        = 'POST';
        $protocol      = '1.0';

        $request = new ServerRequest(
            $serverParams,
            $cookieParams,
            $queryParams,
            $uploadedFiles,
            $attributes,
            $parsedBody,
            $body,
            $headers,
            $uri,
            $method,
            $protocol
        );
        $this->assertSame($serverParams,  $request->getServerParams());
        $this->assertSame($cookieParams,  $request->getCookieParams());
        $this->assertSame($queryParams,   $request->getQueryParams());
        $this->assertSame($uploadedFiles, $request->getUploadedFiles());
        $this->assertSame($attributes,    $request->getAttributes());
        $this->assertSame($parsedBody,    $request->getParsedBody());
        $this->assertSame($body,          $request->getBody());
        $this->assertSame($headers,       $request->getHeaders());
        $this->assertSame($uri,           $request->getUri());
        $this->assertSame($method,        $request->getMethod());
        $this->assertSame($protocol,      $request->getProtocolVersion());
    }

    public function testGetServerParamsReturnsEmptyArrayWhenParamsNotSpecified()
    {
        $request = new ServerRequest();
        $this->assertEquals([], $request->getServerParams());
    }

    public function testGetServerParamReturnsTheParameterValue()
    {
        $params  = ['foo' => 'bar'];
        $request = new ServerRequest($params);
        $this->assertSame('bar', $request->getServerParam('foo'));
    }

    public function testGetServerParamReturnsDefault()
    {
        $request = new ServerRequest();
        $this->assertSame('bar', $request->getServerParam('foo', 'bar'));
    }

    public function testGetCookieParamsReturnsEmptyArrayWhenParamsNotSpecified()
    {
        $request = new ServerRequest();
        $this->assertEquals([], $request->getCookieParams());
    }

    public function testWithCookieParamsReturnsNewInstance()
    {
        $request = new ServerRequest();

        $new = $request->withCookieParams(['foo' => 'bar']);
        $this->assertNotSame($new, $request);
    }

    public function testGetCookieParamReturnsTheParameterValue()
    {
        $params  = ['foo' => 'bar'];
        $request = new ServerRequest(null, $params);
        $this->assertSame('bar', $request->getCookieParam('foo'));
    }

    public function testGetCookieParamReturnsDefault()
    {
        $request = new ServerRequest();
        $this->assertSame('bar', $request->getCookieParam('foo', 'bar'));
    }

    public function testWithCookieParamsSetParamsToNewInstance()
    {
        $request = new ServerRequest();

        $cookies = ['foo' => 'bar'];
        $new     = $request->withCookieParams($cookies);
        $this->assertEquals($cookies, $new->getCookieParams());
    }

    public function testGetQueryParamsReturnsEmptyArrayWhenParamsNotSpecified()
    {
        $request = new ServerRequest();
        $this->assertEquals([], $request->getQueryParams());
    }

    public function testGetQueryParamReturnsTheParameterValue()
    {
        $params  = ['foo' => 'bar'];
        $request = new ServerRequest(null, null, $params);
        $this->assertSame('bar', $request->getQueryParam('foo'));
    }

    public function testGetQueryParamReturnsDefault()
    {
        $request = new ServerRequest();
        $this->assertSame('bar', $request->getQueryParam('foo', 'bar'));
    }

    public function testWithQueryParamsReturnsNewInstance()
    {
        $request = new ServerRequest();

        $new = $request->withQueryParams(['foo' => 'bar']);
        $this->assertNotSame($new, $request);
    }

    public function testWithQueryParamsSetParamsToNewInstance()
    {
        $request = new ServerRequest();

        $query = ['foo' => 'bar'];
        $new   = $request->withQueryParams($query);
        $this->assertEquals($query, $new->getQueryParams());
    }

    public function testGetUploadedFilesReturnsEmptyArrayWhenFilesNotSpecified()
    {
        $request = new ServerRequest();
        $this->assertEquals([], $request->getUploadedFiles());
    }

    public function testWithUploadedFilesReturnsNewInstance()
    {
        $request = new ServerRequest();

        $uploadedFiles = ['foo' => new UploadedFile('foo', 'ban', 'bar', 100, 0)];
        $new           = $request->withUploadedFiles($uploadedFiles);
        $this->assertNotSame($new, $request);
    }

    public function testWithUploadedFilesSetFilesToNewInstance()
    {
        $request = new ServerRequest();

        $files = [
            'foo' => [
                'name'     => ['bar' => ['baz' => 'cor']],
                'type'     => ['bar' => ['baz' => 'con']],
                'tmp_name' => ['bar' => ['baz' => 'cot']],
                'error'    => ['bar' => ['baz' => 0]],
                'size'     => ['bar' => ['baz' => 100]],
            ],
        ];
        $uploadedFiles = UploadedFilesFactory::make($files);
        $instance      = $uploadedFiles['foo']['bar']['baz'];

        $new    = $request->withUploadedFiles($uploadedFiles);
        $result = $new->getUploadedFiles();
        $this->assertTrue(isset($result['foo']['bar']['baz']));
        $this->assertSame($instance, $result['foo']['bar']['baz']);
    }

    public function testInvalidUploadedFilesThrowsException()
    {
        $request = new ServerRequest();

        $uploadedFiles = [
            'foo' => new UploadedFile('foo', 'ban', 'bar', 100, 0),
            'bar' => [
                'name'     => 'con',
                'type'     => 'cop',
                'tmp_name' => 'cor',
                'error'    => 0,
                'size'     => 100,
            ],
        ];
        $this->setExpectedException('InvalidArgumentException');
        $request->withUploadedFiles($uploadedFiles);
    }

    public function testGetParsedBodyReturnsNullWhenParsedBodyNotSpecified()
    {
        $request = new ServerRequest();
        $this->assertNull($request->getParsedBody());
    }

    public function testWithParsedBodyReturnsNewInstance()
    {
        $request = new ServerRequest();

        $new = $request->withParsedBody(['foo' => 'bar']);
        $this->assertNotSame($new, $request);
    }

    public function testWithParsedBodySetParsetBodyToNewInstance()
    {
        $request = new ServerRequest();

        $parsedBody = ['foo' => 'bar'];
        $new        = $request->withParsedBody($parsedBody);
        $this->assertEquals($parsedBody, $new->getParsedBody());
    }

    public function testInvalidParsedBodyThrowsException()
    {
        $request = new ServerRequest();
        $this->setExpectedException('InvalidArgumentException');
        $request->withParsedBody(false);
    }

    public function testGetAttributesReturnsEmptyArrayWhenAttributesNotSpecified()
    {
        $request = new ServerRequest();
        $this->assertEquals([], $request->getAttributes());
    }

    public function testGetAttributeReturnsDefaultWhenAttributeNotExists()
    {
        $request = new ServerRequest();
        $this->assertEquals('bar', $request->getAttribute('foo', 'bar'));
    }

    public function testGetAttributeReturnsAttribute()
    {
        $request = new ServerRequest();

        $new = $request->withAttribute('foo', 'bar');
        $this->assertEquals('bar', $new->getAttribute('foo'));
    }

    public function testWithAttribute()
    {
        $request = new ServerRequest(
            null,
            null,
            null,
            null,
            ['foo' => 'bar']
        );

        $new = $request->withAttribute('foo', 'bat');
        $this->assertInstanceOf('Psr\Http\Message\ServerRequestInterface', $new);
        $this->assertEquals('bat', $new->getAttribute('foo'));
        $this->assertNotSame($new, $request);
    }

    public function testWithAttributes()
    {
        $request    = new ServerRequest();
        $attributes = [
            'foo' => 'bar',
            'bat' => 'baz',
        ];
        $new = $request->withAttributes($attributes);
        $this->assertInstanceOf('Psr\Http\Message\ServerRequestInterface', $new);
        $this->assertSame($attributes, $new->getAttributes());
        $this->assertNotSame($new, $request);
    }

    public function testWithAddedAttributes()
    {
        $request = new ServerRequest(
            null,
            null,
            null,
            null,
            ['foo' => 'bar', 'bat' => 'ban']
        );

        $new = $request->withAddedAttributes(['bat' => 'baz', 'com' => 'cot']);
        $this->assertInstanceOf('Psr\Http\Message\ServerRequestInterface', $new);
        $expects = [
            'foo' => 'bar',
            'bat' => 'baz',
            'com' => 'cot',
        ];
        $this->assertSame($expects, $new->getAttributes());
        $this->assertNotSame($new, $request);
    }

    public function testWithoutAttribute()
    {
        $request = new ServerRequest(
            null,
            null,
            null,
            null,
            ['foo' => 'bar']
        );

        $new = $request->withoutAttribute('foo');
        $this->assertInstanceOf('Psr\Http\Message\ServerRequestInterface', $new);
        $this->assertNull($new->getAttribute('foo'));
        $this->assertNotSame($new, $request);
    }

    public function invalidBodyDataProvider()
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
     * @dataProvider invalidBodyDataProvider
     */
    public function testInvalidBodyRaiseException($body)
    {
        $this->setExpectedException('InvalidArgumentException');
        $request = new ServerRequest(
            null,
            null,
            null,
            null,
            null,
            null,
            $body
        );
    }
}
