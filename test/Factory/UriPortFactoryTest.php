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

use Es\Http\Factory\UriPortFactory;

class UriPortFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function invalidPortDataProvider()
    {
        $ports = [
            false,
            true,
            [],
            new \stdClass,
            0,
            65536,
        ];
        $return = [];
        foreach ($ports as $port) {
            $return[] = [$port];
        }
        return $return;
    }

    /**
     * @dataProvider invalidPortDataProvider
     */
    public function testSetForcedPortRaiseExceptionIfInvalidPortProvided($port)
    {
        $this->setExpectedException('InvalidArgumentException');
        UriPortFactory::setForcedPort($port);
    }

    public function testSetForcedPortSetsPort()
    {
        $port = '8080';
        UriPortFactory::setForcedPort($port);
        $this->assertEquals($port, UriPortFactory::getForcedPort());
    }

    public function testGetForcedPortIfPortResets()
    {
        UriPortFactory::setForcedPort(null);
        $this->assertNull(UriPortFactory::getForcedPort());
    }

    public function testMakeReturnsForcedPort()
    {
        $port = '8080';
        UriPortFactory::setForcedPort($port);
        $server = [
            'SERVER_PORT' => '80',
        ];
        $this->assertEquals($port, UriPortFactory::make($server));
    }

    public function testRetrieveReturnsPortByDefault()
    {
        $server = [
            'foo' => 'bar',
        ];
        $this->assertSame(80, UriPortFactory::retrieve($server));
    }

    public function testRetrieveIgnoreForcedPort()
    {
        UriPortFactory::setForcedPort('8080');
        $server = [
            'SERVER_PORT' => '80',
        ];
        $this->assertSame(80, UriPortFactory::retrieve($server));
    }

    public function testRetrieveRetrievesPort()
    {
        $server = [
            'SERVER_PORT' => '443',
        ];
        $this->assertSame(443, UriPortFactory::retrieve($server));
    }

    public function testRetrieveRetrievesPortFromGlobalServer()
    {
        $_SERVER = [
            'SERVER_PORT' => '443',
        ];
        $this->assertSame(443, UriPortFactory::retrieve());
    }
}
