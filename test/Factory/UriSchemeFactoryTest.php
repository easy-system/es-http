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

use Es\Http\Factory\UriSchemeFactory;

class UriSchemeFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function invalidSchemeDataProvider()
    {
        $schemes = [
            true,
            false,
            100,
            [],
            new \stdClass(),
        ];
        $return = [];
        foreach ($schemes as $scheme) {
            $return[] = [$scheme];
        }

        return $return;
    }

    /**
     * @dataProvider invalidSchemeDataProvider
     */
    public function testSetForcedSchemeRaiseExceptionIfInvalidTypeOfSchemeReceived($scheme)
    {
        $this->setExpectedException('InvalidArgumentException');
        UriSchemeFactory::setForcedScheme($scheme);
    }

    public function testGetForcedSchemeReturnsForcedScheme()
    {
        $scheme = 'https';
        UriSchemeFactory::setForcedScheme($scheme);
        $this->assertSame($scheme, UriSchemeFactory::getForcedScheme());
    }

    public function testMakeReturnsForcedSchemeIfAny()
    {
        $scheme = 'https';
        UriSchemeFactory::setForcedScheme($scheme);
        $server = [
            'foo' => 'bar',
        ];
        $this->assertSame($scheme, UriSchemeFactory::make($server));
    }

    public function testRetrieveIgnoreForcedScheme()
    {
        $scheme = 'https';
        UriSchemeFactory::setForcedScheme($scheme);
        $server = [
            'foo' => 'bar',
        ];
        $this->assertNotEquals($scheme, UriSchemeFactory::retrieve($server));
    }

    public function testRetrieveReturnsHttpByDefault()
    {
        $server = [
            'foo' => 'bar',
        ];
        $this->assertSame('http', UriSchemeFactory::retrieve($server));
    }

    public function testRetrieveRetrievesHttpsIfAny()
    {
        $server = [
            'HTTPS' => 'on',
        ];
        $this->assertSame('https', UriSchemeFactory::retrieve($server));
        //
        $server = [
            'HTTPS' => 'off',
        ];
        $this->assertSame('http', UriSchemeFactory::retrieve($server));
    }

    public function testRetriveRetrivesShemeFromGlobalServer()
    {
        $_SERVER = [
            'HTTPS' => 'on',
        ];
        $this->assertSame('https', UriSchemeFactory::retrieve());
    }
}
