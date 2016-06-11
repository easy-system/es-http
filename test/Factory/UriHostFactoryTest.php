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

use Es\Http\Factory\UriHostFactory;

class UriHostFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function invalidForcedHostDataProvider()
    {
        $hosts = [
            true,
            false,
            100,
            [],
            new \stdClass(),
        ];
        $return = [];
        foreach ($hosts as $host) {
            $return[] = [$host];
        }
        return $return;
    }

    /**
     * @dataProvider invalidForcedHostDataProvider
     */
    public function testSetForcedHostRaiseExceptionWhenInvalidFormatOfHostReceived($host)
    {
        $this->setExpectedException('InvalidArgumentException');
        UriHostFactory::setForcedHost($host);
    }

    public function testGetForcedHost()
    {
        $host = 'foo';
        UriHostFactory::setForcedHost($host);
        $this->assertSame($host, UriHostFactory::getForcedHost());
    }

    public function testMakeReturnsForcedHostIfAny()
    {
        $host = 'foo';
        $server = [
            'SERVER_NAME' => 'bar',
        ];
        UriHostFactory::setForcedHost($host);
        $this->assertSame($host, UriHostFactory::make($server));
    }

    public function testRetriveIgnoreForcedHost()
    {
        UriHostFactory::setForcedHost('foo');
        $server = [
            'SERVER_NAME' => 'bar',
        ];
        $this->assertSame('bar', UriHostFactory::retrieve($server));
    }

    public function testRetrieveRetrievesHostFromServerName()
    {
        $server = [
            'SERVER_NAME' => 'foo',
            'SERVER_ADDR' => 'bar',
        ];
        $this->assertSame('foo', UriHostFactory::retrieve($server));
    }

    public function testRetrieveRetrievesHostFromServerAddress()
    {
        $server = [
            'SERVER_ADDR' => 'foo',
        ];
        $this->assertSame('foo', UriHostFactory::retrieve($server));
    }

    public function testRetrieveRetrievesHostFromLocalAddress()
    {
        $server = [
            'LOCAL_ADDR' => 'foo',
        ];
        $this->assertSame('foo', UriHostFactory::retrieve($server));
    }

    public function testRetriveRetrievesAddressFromIPv6()
    {
        $server = [
            'SERVER_ADDR' => '2010:836B:4179::836B:4179',
        ];
        $this->assertSame('[2010:836B:4179::836B:4179]', UriHostFactory::retrieve($server));
    }

    public function testRetrieveRetrievesFromGlobalServer()
    {
        $_SERVER = [
            'SERVER_NAME' => 'foo',
        ];
        $this->assertSame('foo', UriHostFactory::retrieve());
    }

    public function testRetrieveRetrievesNullOnFailure()
    {
        $server = [
            'foo' => 'bar',
        ];
        $this->assertNull(UriHostFactory::retrieve($server));
    }
}
