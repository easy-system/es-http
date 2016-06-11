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

use Es\Http\Factory\UriQueryFactory;

class UriQueryFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMakeReturnsNullIfQueryNotFound()
    {
        $server = [
            'foo' => 'bar',
        ];
        $this->assertNull(UriQueryFactory::make($server));
    }

    public function testMakeReturnsQuery()
    {
        $server = [
            'REQUEST_URI' => '/foo?bar=baz&con=cop',
        ];
        $this->assertSame('bar=baz&con=cop', UriQueryFactory::make($server));
    }

    public function testMakeReturnsQueryFromGlobalServer()
    {
        $_SERVER = [
            'REQUEST_URI' => '/foo?bar=baz&con=cop',
        ];
        $this->assertSame('bar=baz&con=cop', UriQueryFactory::make());
    }
}
