<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http\Test\Uploading;

use Es\Http\Uploading\DefaultUploadStrategy;

class DefaultUploadStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $strategy  = new DefaultUploadStrategy();
        $arrayCopy = $strategy->getArrayCopy();

        $this->assertTrue(isset($arrayCopy[100]));
        $this->assertInstanceof('Es\Http\Uploading\MoveStrategy', $arrayCopy[100]);

        $this->assertTrue(isset($arrayCopy[200]));
        $this->assertInstanceof('Es\Http\Uploading\DirectoryStrategy', $arrayCopy[200]);
    }
}
