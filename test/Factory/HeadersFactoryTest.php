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

class HeadersFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMakeContentDirect()
    {
        $server = [
            'HTTP_CONTENT_TYPE'   => 'foo',
            'CONTENT_TYPE'        => 'bar',
            'HTTP_CONTENT_LENGTH' => 'foo',
            'CONTENT_LENGTH'      => 'bar',
            'HTTP_CONTENT_MD5'    => 'foo',
            'CONTENT_MD5'         => 'bar',
        ];

        $expected = [
            'Content-Type'   => 'bar',
            'Content-Length' => 'bar',
            'Content-Md5'    => 'bar',
        ];
        $headers = HeadersFactory::make($server);
        $this->assertSame($expected, $headers);
    }

    public function testMakeFromPrefix()
    {
        $server = [
            'HTTP_CONTENT_TYPE'    => 'foo',
            'HTTP_CONTENT_LENGTH'  => 'foo',
            'HTTP_CONTENT_MD5'     => 'foo',
            'HTTP_AUTHORIZATION'   => 'foo',
            'HTTP_ACCEPT_ENCODING' => 'foo',
        ];

        $expected = [
            'Content-Type'    => 'foo',
            'Content-Length'  => 'foo',
            'Content-Md5'     => 'foo',
            'Authorization'   => 'foo',
            'Accept-Encoding' => 'foo',
        ];
        $headers = HeadersFactory::make($server);
        $this->assertSame($expected, $headers);
    }

    public function testMakeFromGlobalServer()
    {
        $_SERVER = [
           'HTTP_CONTENT_TYPE' => 'foo',
        ];
        $headers  = HeadersFactory::make();
        $expected = [
            'Content-Type' => 'foo',
        ];
        $this->assertSame($expected, $headers);
    }
}
