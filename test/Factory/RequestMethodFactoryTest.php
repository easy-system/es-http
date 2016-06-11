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

use Es\Http\Factory\RequestMethodFactory;

class RequestMethodFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMakeReturnsGetIfUnableToRetrieveRequestMethod()
    {
        $server = ['foo' => 'bar'];
        $method = RequestMethodFactory::make($server);
        $this->assertSame('GET', $method);
    }

    public function testMakeRetrieveMethodFromServer()
    {
        $server = [
            'REQUEST_METHOD' => 'foo',
        ];
        $method = RequestMethodFactory::make($server);
        $this->assertSame('foo', $method);
    }

    public function testMakeFromGlobalServer()
    {
        $_SERVER = [
            'REQUEST_METHOD' => 'foo',
        ];
        $method = RequestMethodFactory::make();
        $this->assertSame('foo', $method);
    }
}
