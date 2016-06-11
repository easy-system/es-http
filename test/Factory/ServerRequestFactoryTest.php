<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http\Test\Factory;

use Es\Http\Factory\HeadersFactory;
use Es\Http\Factory\InputFactory;
use Es\Http\Factory\RequestProtocolFactory;
use Es\Http\Factory\ServerRequestFactory;
use Es\Http\Factory\UploadedFilesFactory;
use Es\Http\Factory\UriFactory;
use Es\Http\Stream;
use Es\Http\Uri;

class ServerRequestFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMakeCreateServerRequestWithArguments()
    {
        $serverParams  = ['foo' => 'bar'];
        $cookieParams  = ['ban' => 'bar'];
        $queryParams   = ['bar' => 'bat'];
        $uploadedFiles = [
            'foo' => [
                'name'     => 'bar',
                'type'     => 'bat',
                'tmp_name' => 'baz',
                'error'    => 0,
                'size'     => 100,
            ],
        ];
        $attributes = ['baz' => 'con'];
        $parsedBody = ['con' => 'cor'];
        $body       = new Stream();
        $headers    = ['cop' => ['cot', 'coz']];
        $uri        = new Uri();
        $method     = 'POST';
        $protocol   = '1.0';

        $request = ServerRequestFactory::make(
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
        $this->assertInstanceOf('Es\\Http\\ServerRequest', $request);

        $this->assertEquals(
            UploadedFilesFactory::make($uploadedFiles),
            $request->getUploadedFiles()
        );
        $this->assertSame($serverParams,  $request->getServerParams());
        $this->assertSame($cookieParams,  $request->getCookieParams());
        $this->assertSame($queryParams,   $request->getQueryParams());
        $this->assertSame($attributes,    $request->getAttributes());
        $this->assertSame($parsedBody,    $request->getParsedBody());
        $this->assertSame($body,          $request->getBody());
        $this->assertSame($headers,       $request->getHeaders());
        $this->assertSame($uri,           $request->getUri());
        $this->assertSame($method,        $request->getMethod());
        $this->assertSame($protocol,      $request->getProtocolVersion());
    }

    public function testMakeCreateInstanceOfServerRequestWithoutArguments()
    {
        $request = ServerRequestFactory::make();
        $this->assertInstanceOf('Es\\Http\\ServerRequest', $request);
    }

    public function testMakeSetsServerParamsFromServer()
    {
        $request = ServerRequestFactory::make();
        $this->assertEquals($_SERVER, $request->getServerParams());
    }

    public function testMakeSetsCookieParamsFromCookies()
    {
        $_COOKIE = ['foo' => 'bar'];
        $request = ServerRequestFactory::make();
        $this->assertEquals($_COOKIE, $request->getCookieParams());
    }

    public function testMakeSetsQueryParamsFromGet()
    {
        $_GET    = ['foo' => 'bar'];
        $request = ServerRequestFactory::make();
        $this->assertEquals($_GET, $request->getQueryParams());
    }

    public function testMakeCreateUploadedFilesFromFiles()
    {
        $_FILES = [
            'foo' => [
                'name'     => 'bar',
                'type'     => 'bat',
                'tmp_name' => 'baz',
                'error'    => 0,
                'size'     => 100,
            ],
        ];
        $request = ServerRequestFactory::make();
        $this->assertEquals($request->getUploadedFiles(), UploadedFilesFactory::make());
    }

    public function testMakeSetsAttributesFromEnv()
    {
        $_ENV    = ['foo' => 'bar'];
        $request = ServerRequestFactory::make();
        $this->assertEquals($_ENV, $request->getAttributes());
    }

    public function testMakeCreateInput()
    {
        $source = Stream::fopen();
        fwrite($source, 'Lorem ipsum dolor sit amet');
        InputFactory::setSource($source);
        $input = InputFactory::make();

        $request = ServerRequestFactory::make();
        $body    = $request->getBody();
        $this->assertEquals($body->getContents(), $input->getContents());

        InputFactory::setSource(null);
    }

    public function testMakeCreateUri()
    {
        $request = ServerRequestFactory::make();
        $uri     = $request->getUri();
        $this->assertEquals(UriFactory::make(), $uri);
    }

    public function testMakeCreateHeaders()
    {
        $_SERVER = array_merge($_SERVER, ['HTTP_X_TEST' => 'Lorem ipsum dolor sit amet']);
        $headers = HeadersFactory::make();
        $this->assertTrue(isset($headers['X-Test']));

        $request = ServerRequestFactory::make();
        $this->assertEquals($request->getHeader('X-Test'), ['Lorem ipsum dolor sit amet']);
    }

    public function testMakeCreateProtocolVersion()
    {
        $request = ServerRequestFactory::make();
        $this->assertEquals($request->getProtocolVersion(), RequestProtocolFactory::getVersion());
    }
}
