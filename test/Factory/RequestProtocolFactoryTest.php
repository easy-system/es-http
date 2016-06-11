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

use Es\Http\Factory\RequestProtocolFactory;

class RequestProtocolFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetVersionRaiseExceptionIfTheVersionIsUnrecognized()
    {
        $server = [
            'SERVER_PROTOCOL' => 'foo',
        ];
        $this->setExpectedException('UnexpectedValueException');
        RequestProtocolFactory::getVersion($server);
    }

    public function protocolVersionsDataProvider()
    {
        $versions = [
            'HTTP/1.0' => '1.0',
            'HTTP/1.1' => '1.1',
            'HTTP/2.0' => '2.0',
        ];
        $return = [];
        foreach ($versions as $protocol => $expected) {
            $return[] = [$protocol, $expected];
        }
        return $return;
    }

    /**
     * @dataProvider protocolVersionsDataProvider
     */
    public function testGetVersionReturnsProtocolVersion($protocol, $expected)
    {
        $server = [
            'SERVER_PROTOCOL' => $protocol,
        ];
        $this->assertSame($expected, RequestProtocolFactory::getVersion($server));
    }

    public function testMakeReturnsProtocol()
    {
        $protocol = 'HTTP/1.0';
        $server = [
            'SERVER_PROTOCOL' => $protocol,
        ];
        $this->assertSame($protocol, RequestProtocolFactory::make($server));
    }

    public function testMakeReturnsProtocolByDefault()
    {
        $server = [
            'foo' => 'bar',
        ];
        $this->assertSame('HTTP/1.1', RequestProtocolFactory::make($server));
    }

    public function testMakeFromGlobalServer()
    {
        $_SERVER = [
            'SERVER_PROTOCOL' => 'HTTP/1.0',
        ];
        $this->assertSame('HTTP/1.0', RequestProtocolFactory::make());
    }
}
