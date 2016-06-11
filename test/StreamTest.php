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

class StreamTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorCreateStreamWithoutResource()
    {
        $stream = new Stream(false);
        $this->assertNull($stream->getResource());
    }

    public function testConstructorCreateResourceByDefault()
    {
        $stream = new Stream();
        $this->assertTrue(is_resource($stream->getResource()));
    }

    public function testConstructorSetResource()
    {
        $resource = fopen('php://memory', 'w+b');
        $stream   = new Stream($resource);
        $this->assertSame($resource, $stream->getResource());
    }

    public function testAttachResource()
    {
        $resource = fopen('php://memory', 'w+b');
        $stream   = new Stream(false);
        $stream->attach($resource);
        $this->assertSame($resource, $stream->getResource());
    }

    public function testDetachResource()
    {
        $resource = fopen('php://memory', 'w+b');
        $stream   = new Stream($resource);
        $this->assertSame($stream->detach(), $resource);
        $this->assertNull($stream->getResource());
    }

    public function testCloseResource()
    {
        $stream = new Stream();
        $stream->close();
        $this->assertNull($stream->getResource());
    }

    public function testToStringReturnsEmptyStringWhenResourceNotPresent()
    {
        $stream = new Stream(false);
        $this->assertEquals('', (string) $stream);
    }

    public function testToStringReturnsEmptyStringWhenStreamIsNotReadable()
    {
        $resource = Stream::fopen('php://temp', 'w');
        fwrite($resource, 'Lorem ipsum dolor sit amet');
        $stream = new Stream($resource);
        $this->assertEquals('', (string) $stream);
    }

    public function testTOStringReturnsEmptyStringOnError()
    {
        $stream = $this->getMock(Stream::CLASS, ['isSeekable']);
        $stream
            ->expects($this->once())
            ->method('isSeekable')
            ->will($this->returnValue(false));

        $this->assertSame('', (string) $stream);
    }

    public function testToStringReturnsFullContents()
    {
        $string = 'Lorem ipsum dolor sit amet';
        $stream = Stream::make($string);
        $stream->seek(4);
        $this->assertEquals($string, (string) $stream);
    }

    public function testSizeReturnsNullWhenResourceNotPresenent()
    {
        $stream = new Stream(false);
        $this->assertNull($stream->getSize());
    }

    public function testSizeReturnsStreamSize()
    {
        $stream   = Stream::make('Lorem ipsum dolor sit amet');
        $resource = $stream->getResource();
        $stat     = fstat($resource);
        $this->assertEquals($stat['size'], $stream->getSize());
    }

    public function testTellReturnsCurrentPosition()
    {
        $stream   = Stream::make('Lorem ipsum dolor sit amet');
        $resource = $stream->getResource();
        fseek($resource, 3);
        $this->assertEquals(3, $stream->tell());
    }

    public function testTellThrowsExceptionWhenResourceNotPresent()
    {
        $stream = new Stream(false);
        $this->setExpectedException('RuntimeException');
        $stream->tell();
    }

    public function testTellThrowsExceptionWhenError()
    {
        $stream   = new Stream();
        $resource = $stream->getResource();
        fseek($resource, 100);
        $this->setExpectedException('RuntimeException');
        $stream->tell();
    }

    public function testEofReturnsTrueWhenResourceNotPresents()
    {
        $stream = new Stream(false);
        $this->assertTrue($stream->eof());
    }

    public function testEofReturnsFalseWhenNotEndOfStream()
    {
        $stream   = Stream::make('Lorem ipsum dolor sit amet');
        $resource = $stream->getResource();
        fseek($resource, 3);
        $this->assertFalse($stream->eof());
    }

    public function testEofReturnsTrueWhenEndOfStream()
    {
        $stream   = Stream::make('Lorem ipsum dolor sit amet');
        $resource = $stream->getResource();
        stream_get_contents($resource);
        $this->assertTrue($stream->eof());
    }

    public function testIsSeekebleReturnsFalseWhenResourceNotPresent()
    {
        $stream = new Stream(false);
        $this->assertFalse($stream->isSeekable());
    }

    public function testIsSeekableReturnsTrueForSeekableStream()
    {
        $resource = Stream::fopen('php://memory', 'w');
        $stream   = new Stream($resource);
        $this->assertTrue($stream->isSeekable());
    }

    public function testIsSeekableReturnsFalseForNonSeekableStream()
    {
        $stream = new Stream('http://google.com', 'rb');
        $this->assertFalse($stream->isSeekable());
    }

    public function testSeekSetNewPosition()
    {
        $stream = Stream::make('Lorem ipsum dolor sit amet');
        $this->assertTrue($stream->seek(3));
        $resource = $stream->getResource();
        $this->assertEquals(3, ftell($resource));
    }

    public function testSeekThrowsExceptionWhenResourceNotPresents()
    {
        $stream = new Stream(false);
        $this->setExpectedException('RuntimeException');
        $stream->seek(100);
    }

    public function testSeekThrowsExceptionWhenStreamIsNotSeekable()
    {
        $stream = new Stream('http://google.com', 'rb');
        $this->setExpectedException('RuntimeException');
        $stream->seek(100);
    }

    public function testSeekThrowsExceptionOnFailure()
    {
        $stream = Stream::make('Lorem ipsum dolor sit amet');
        $this->setExpectedException('RuntimeException');
        $stream->seek(1000);
    }

    public function testRewindRewindsStream()
    {
        $stream   = Stream::make('Lorem ipsum dolor sit amet');
        $resource = $stream->getResource();
        fseek($resource, 3);
        $stream->rewind();
        $this->assertEquals(0, ftell($resource));
    }

    public function testIsWritableReturnsFalseWhenResourceNotPresents()
    {
        $stream = new Stream(false);
        $this->assertFalse($stream->isWritable());
    }

    public function testIsWritableReturnsTrueWhenStreamIsWritable()
    {
        $stream = new Stream();
        $this->assertTrue($stream->isWritable());
    }

    public function testIsWritableReturnsFalseWhenStreamIsNotWritable()
    {
        $resource = Stream::fopen('php://temp', 'rb');
        $stream   = new Stream($resource);
        $this->assertFalse($stream->isWritable());
    }

    public function testWriteThrowsExceptionWhenResourceNotPresents()
    {
        $stream = new Stream(false);
        $this->setExpectedException('RuntimeException');
        $stream->write('Lorem ipsum dolor sit amet');
    }

    public function testWriteThrowsExceptionWhenStreamIsNotWritable()
    {
        $stream = new Stream('http://google.com', 'rb');
        $this->setExpectedException('RuntimeException');
        $stream->write('Lorem ipsum dolor sit amet');
    }

    public function testIsReadableReturnsFalseWhenResourceNotPresents()
    {
        $stream = new Stream(false);
        $this->assertFalse($stream->isReadable());
    }

    public function testIsReadableReturnsTrueWhenStreamIsReadable()
    {
        $stream = new Stream('http://google.com', 'rb');
        $this->assertTrue($stream->isReadable());
    }

    public function testIsReadableReturnsFalseWhenStreamIsNotReadable()
    {
        $resource = Stream::fopen('php://temp', 'w');
        $stream   = new Stream($resource);
        $this->assertFalse($stream->isReadable());
    }

    public function testReadThrowsExceptionWhenResourceNotPresents()
    {
        $stream = new Stream(false);
        $this->setExpectedException('RuntimeException');
        $stream->read(1);
    }

    public function testReadThrowsExceptionWhenStreamIsNotReadable()
    {
        $resource = Stream::fopen('php://temp', 'w');
        $stream   = new Stream($resource);
        $this->setExpectedException('RuntimeException');
        $stream->read(1);
    }

    public function testGetContentsReturnsEmptyStringWhenResourceNotPresents()
    {
        $stream = new Stream(false);
        $this->assertEquals('', $stream->getContents());
    }

    public function testGetContentsReturnEmptyStringWhenResourceIsNotReadable()
    {
        $resource = Stream::fopen('php://temp', 'w');
        $stream   = new Stream($resource);
        $this->assertEquals('', $stream->getContents());
    }

    public function testGetContentsReturnContents()
    {
        $stream   = Stream::make('1234567890');
        $resource = $stream->getResource();
        fseek($resource, 5);
        $this->assertEquals('67890', $stream->getContents());
    }

    public function testGetMetadataReturnsNullWhenResourceNotPresents()
    {
        $stream = new Stream(false);
        $this->assertNull($stream->getMetadata());
    }

    public function testGetMetadataReturnsResourceMetadataWhenKeyNotSpecified()
    {
        $stream   = new Stream();
        $resource = $stream->getResource();
        $this->assertEquals(stream_get_meta_data($resource), $stream->getMetadata());
    }

    public function testGetMetadataReturnsValueOfSpecifiedKey()
    {
        $stream   = new Stream();
        $resource = $stream->getResource();
        $metadata = stream_get_meta_data($resource);
        $this->assertEquals($metadata['uri'], $stream->getMetadata('uri'));
        $this->assertEquals($metadata['mode'], $stream->getMetadata('mode'));
        $this->assertEquals($metadata['seekable'], $stream->getMetadata('seekable'));
    }

    public function testGetMetadataReturnsNullWhenSpecifiedKeyNotFound()
    {
        $stream = new Stream();
        $this->assertNull($stream->getMetadata('non-existent-key'));
    }
}
