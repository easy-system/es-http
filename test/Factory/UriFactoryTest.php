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

use Es\Http\Factory\UriFactory;

class UriFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMakeWithAbsoluteAddress()
    {
        $server = [
            'HTTPS'       => 'on',
            'SERVER_NAME' => 'example.com',
            'SERVER_PORT' => '8080',
            'REQUEST_URI' => 'foo?baz=bar',
        ];
        $uri = UriFactory::make($server);
        $this->assertInstanceOf('Es\Http\Uri', $uri);
        $this->assertSame('https://example.com:8080/foo?baz=bar', (string) $uri);
    }

    public function testMakeWithRelativeAddress()
    {
        $server = [
            'HTTPS'       => 'on',
            'REQUEST_URI' => 'foo?baz=bar',
        ];
        $uri = UriFactory::make($server);
        $this->assertInstanceOf('Es\Http\Uri', $uri);
        $this->assertSame('https:foo?baz=bar', (string) $uri);
    }
}
