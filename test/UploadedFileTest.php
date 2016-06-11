<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http\Test;

use Es\Http\Stream;
use Es\Http\UploadedFile;

class UploadedFileTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $file = new UploadedFile(
            'foo',
            'bar',
            'baz',
            100,
            0
        );
        $this->assertEquals('foo', $file->getClientFilename());
        $this->assertEquals('bar', $file->getTempName());
        $this->assertEquals('baz', $file->getClientMediaType());
        $this->assertEquals(100,   $file->getSize());
        $this->assertEquals(0,     $file->getError());
    }

    public function testSetStream()
    {
        $stream = new Stream();
        $file   = new UploadedFile();
        $file->setStream($stream); // Interesting architecture ;)
        $this->assertSame($stream, $file->getStream());
    }

    public function testGetStreamCreateStreamFromTempName()
    {
        $file = new UploadedFile(null, 'php://temp');
        $this->assertInstanceOf('Psr\Http\Message\StreamInterface', $file->getStream());
    }

    public function testGetStreamRaiseExceptionWhenStreamWasNotSetAndTempNameNotSpecified()
    {
        $file = new UploadedFile();
        $this->setExpectedException('RuntimeException');
        $file->getStream();
    }

    public function testGetStreamRaiseExceptionWhenFileIsAlreadyMoved()
    {
        $file = new UploadedFile();
        $stream = new Stream();
        $file->setStream($stream);
        $strategy = $this->getMock('Es\Http\Uploading\DefaultUploadStrategy');
        $file->setUploadStrategy($strategy);
        $strategy
            ->expects($this->atLeastOnce())
            ->method('__invoke');
        $file->moveTo('foo');
        $this->setExpectedException('RuntimeException');
        $file->getStream();
    }

    public function testGetClientFileNameReturnsNullIfNothingWasSet()
    {
        $name = '';
        $file = new UploadedFile($name);
        $this->assertNull($file->getClientFilename());
        //
        $emptyFile = new UploadedFile();
        $this->assertNull($emptyFile->getClientFilename());
    }

    public function testGetTempNameReturnsNullIfNothingWasSet()
    {
        $tempName = '';
        $file = new UploadedFile(null, $tempName);
        $this->assertNull($file->getTempName());
        //
        $emptyFile = new UploadedFile();
        $this->assertNull($emptyFile->getTempName());
    }

    public function testGetClientMediaTypeReturnsNullIfNothingWasSet()
    {
        $mime = '';
        $file = new UploadedFile(null, null, $mime);
        $this->assertNull($file->getClientMediaType());
        //
        $emptyFile = new UploadedFile();
        $this->assertNull($emptyFile->getClientMediaType());
    }

    public function testGetSizeReturnsNullIfNothingWasSet()
    {
        $size = 0;
        $file = new UploadedFile(null, null, null, $size);
        $this->assertNull($file->getSize());
        //
        $emptyFile = new UploadedFile();
        $this->assertNull($emptyFile->getSize());
    }

    public function testGetErrorReturnValueIfNothingWasSet()
    {
        $file = new UploadedFile();
        $this->assertSame(0, $file->getError());
    }

    public function nonStringDataProvider()
    {
        $values = [
            true,
            ['name'],
            10
        ];
        $return = [];
        foreach ($values as $value) {
            $return[] = [$value];
        }
        return $return;
    }

    /**
     * @dataProvider nonStringDataProvider
     */
    public function testNonStringClientFileNameRaisesException($clientFileName)
    {
        $this->setExpectedException('InvalidArgumentException');
        $file = new UploadedFile($clientFileName);
    }

    /**
     * @dataProvider nonStringDataProvider
     */
    public function testNonStringTempNameRaisesException($tempName)
    {
        $this->setExpectedException('InvalidArgumentException');
        $file = new UploadedFile(null, $tempName);
    }

    /**
     * @dataProvider nonStringDataProvider
     */
    public function testNonStringMediaTypeRaisesException($mime)
    {
        $this->setExpectedException('InvalidArgumentException');
        $file = new UploadedFile(null, null, $mime);
    }

    public function nonIntegerDataProvider()
    {
        $values = [
            true,
            'size',
            [100],
        ];
        $return = [];
        foreach ($values as $value) {
            $return[] = [$value];
        }
        return $return;
    }

    /**
     * @dataProvider nonIntegerDataProvider
     */
    public function testNonIntegerSizeRisesException($size)
    {
        $this->setExpectedException('InvalidArgumentException');
        $file = new UploadedFile(null, null, null, $size);
    }

    public function invalidErrorTypeDataProvider()
    {
        $values = [
            -5,
            12,
            'error',
            [3],
        ];
        $return = [];
        foreach ($values as $value) {
            $return[] = [$value];
        }
        return $return;
    }

    /**
     * @dataProvider invalidErrorTypeDataProvider
     */
    public function testInvalidTypeOfErrorRaisesException($error)
    {
        $this->setExpectedException('InvalidArgumentException');
        $file = new UploadedFile(null, null, null, null, $error);
    }

    public function testGetStrategyReturnsDefaultStrategyByDefault()
    {
        $file = new UploadedFile();
        $this->assertInstanceOf('Es\Http\Uploading\DefaultUploadStrategy', $file->getUploadStrategy());
    }

    public function testMoveToRaiseExceptionWhenFileIsAlreadyMoved()
    {
        $file = new UploadedFile();
        $strategy = $this->getMock('Es\Http\Uploading\DefaultUploadStrategy');
        $file->setUploadStrategy($strategy);
        $strategy
            ->expects($this->atLeastOnce())
            ->method('__invoke');
        $file->moveTo('foo');
        $this->setExpectedException('RuntimeException');
        $file->moveTo('bar');
    }

    public function testMoveToPassArgumentsToStrategy()
    {
        $file = new UploadedFile();
        $strategy = $this->getMock('Es\Http\Uploading\DefaultUploadStrategy');
        $file->setUploadStrategy($strategy);
        $strategy
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->identicalTo($file),
                $this->isInstanceOf('Es\Http\Uploading\UploadTargetInterface')
            );
        $file->moveTo('foo');
    }

    public function testMoveToSetTargetToUploadTarget()
    {
        $file = new UploadedFile();
        $strategy = $this->getMock('Es\Http\Uploading\DefaultUploadStrategy');
        $file->setUploadStrategy($strategy);
        $strategy
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->identicalTo($file),
                $this->callback(function($target) {
                    return 'foo' === (string) $target;
                })
            );
        $file->moveTo('foo');
    }

    public function testMoveToSetsOptionsToStrategy()
    {
        $file = new UploadedFile();
        $strategy = $this->getMock('Es\Http\Uploading\DefaultUploadStrategy');
        $file->setUploadStrategy($strategy);
        $strategy
            ->expects($this->once())
            ->method('setOptions')
            ->with(
                $this->isInstanceOf('Es\Http\Uploading\UploadOptionsInterface')
            );

        $file->moveTo('foo', ['baz' => 'bar']);
    }

    public function testMoveToSetOptionsToUploadOptions()
    {
        $file = new UploadedFile();
        $strategy = $this->getMock('Es\Http\Uploading\DefaultUploadStrategy');
        $file->setUploadStrategy($strategy);
        $strategy
            ->expects($this->once())
            ->method('setOptions')
            ->with(
                $this->callback(function($options) {
                    return ['baz' => 'bar'] === $options->getArrayCopy();
                })
            );

        $file->moveTo('foo', ['baz' => 'bar']);
    }
}
