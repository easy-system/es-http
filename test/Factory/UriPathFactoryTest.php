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

use Es\Http\Factory\UriPathFactory;

class UriPathFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMakeReturnsNullIfPathNotFound()
    {
        $server = [
            'foo' => 'bar',
        ];
        $this->assertNull(UriPathFactory::make($server));
    }

    public function testMakeReturnsPath()
    {
        $server = [
            'REQUEST_URI' => '/foo/bar?baz=ban',
        ];
        $this->assertSame('/foo/bar', UriPathFactory::make($server));
    }

    public function testMakeReturnsPathFromGlobalServer()
    {
        $_SERVER = [
            'REQUEST_URI' => '/foo/bar?baz=ban',
        ];
        $this->assertSame('/foo/bar', UriPathFactory::make());
    }
}
