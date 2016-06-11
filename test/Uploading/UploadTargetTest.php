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

use Es\Http\Uploading\UploadTarget;

class UploadTargetTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorSetTarget()
    {
        $target = new UploadTarget('foo');
        $this->assertEquals('foo', $target->get());
    }

    public function testSetSetsTarget()
    {
        $target = new UploadTarget('foo');
        $target->set('bar');
        $this->assertEquals('bar', $target->get());
    }

    public function invalidTargetDataProvider()
    {
        $targets = [
            '',
            [],
            false,
            true,
        ];
        $return = [];
        foreach ($targets as $target) {
            $return[] = [$target];
        }

        return $return;
    }

    /**
     * @dataProvider invalidTargetDataProvider
     */
    public function testInvalidTargetRaiseException($value)
    {
        $this->setExpectedException('InvalidArgumentException');
        $target = new UploadTarget($value);
    }

    public function testToStringReturnsTarget()
    {
        $target = new UploadTarget('foo');
        $this->assertEquals('foo', (string) $target);
    }
}
