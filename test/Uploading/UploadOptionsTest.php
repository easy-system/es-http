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

use Es\Http\Uploading\UploadOptions;

class UploadOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorSetOptionsFromArray()
    {
        $source  = ['foo' => 'bar'];
        $options = new UploadOptions($source);
        $this->assertEquals($source, $options->getArrayCopy());
    }

    public function testConstructorSetOptionsFromStdClass()
    {
        $source  = ['foo' => 'bar'];
        $object  = (object) $source;
        $options = new UploadOptions($object);
        $this->assertEquals($source, $options->getArrayCopy());
    }

    public function testConstructorSetOptionsFromTraversable()
    {
        $source      = ['foo' => 'bar'];
        $traversable = new UploadOptions($source);
        $options     = new UploadOptions($traversable);
        $this->assertEquals($source, $options->getArrayCopy());
    }

    public function testConstructorRaiseExceptionIfInvalidTypeOfOptionsProvided()
    {
        $this->setExpectedException('InvalidArgumentException');
        $options = new UploadOptions(false);
    }

    public function testGetIteratorReturnsOptionsIterator()
    {
        $source   = ['foo' => 'bar'];
        $options  = new UploadOptions($source);
        $iterator = $options->getIterator();
        $this->assertInstanceOf('ArrayIterator', $iterator);
        $this->assertEquals($source, $iterator->getArrayCopy());
    }

    public function testGetReturnsValueIfAny()
    {
        $source  = ['foo' => 'bar'];
        $options = new UploadOptions($source);
        $this->assertEquals('bar', $options->get('foo'));
    }

    public function testGetReturnsDefaultIfValueNotExists()
    {
        $options = new UploadOptions([]);
        $this->assertEquals('bar', $options->get('foo', 'bar'));
    }

    public function testSetSetsOption()
    {
        $options = new UploadOptions([]);
        $options->set('foo', 'bar');
        $this->assertEquals('bar', $options->get('foo'));
    }

    public function testAddAddsOptions()
    {
        $options = new UploadOptions([]);
        $source  = ['foo' => 'bar'];
        $options->add($source);
        $this->assertEquals($source, $options->getArrayCopy());
    }
}
