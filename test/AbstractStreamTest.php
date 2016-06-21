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
use Exception;

class AbstractStreamTest extends \PHPUnit_Framework_TestCase
{
    public function testMakeFromResource()
    {
        $resource = fopen('php://temp', 'r+');
        $stream   = Stream::make($resource);
        $this->assertInstanceOf('Es\Http\AbstractStream', $stream);
        $this->assertSame($resource, $stream->getResource());
    }

    public function testMakeFromString()
    {
        $string = 'Lorem ipsum dolor sit amet';
        $stream = Stream::make($string);
        $this->assertInstanceOf('Es\Http\AbstractStream', $stream);
        $this->assertEquals($string, $stream->getContents());
    }

    public function testMakeFromStringObject()
    {
        $exception = new Exception('Lorem ipsum dolor sit amet');
        $stream    = Stream::make($exception);
        $this->assertInstanceOf('Es\Http\AbstractStream', $stream);
        $this->assertEquals((string) $exception, $stream->getContents());
    }

    public function testMakeReturnOriginalAbstractStream()
    {
        $originalStream = new Stream(false);
        $this->assertInstanceOf('Es\Http\AbstractStream', $originalStream);

        $stream = Stream::make($originalStream);
        $this->assertSame($stream, $originalStream);
    }

    public function testMakeThrowsExceptionWhenInvalidResourceProvided()
    {
        $resource = false;
        $this->setExpectedException('InvalidArgumentException');
        Stream::make($resource);
    }

    public function testOpenStreamReturnsTrueOnSuccess()
    {
        $stream = new Stream(false);
        $this->assertTrue($stream->open());
        $this->assertTrue(is_resource($stream->getResource()));
    }

    public function testOpenStreamSetResourceFromContext()
    {
        $resource = fopen('php://temp', 'w+b');
        $stream   = new Stream(false);
        $context  = stream_context_create(
            [Stream::PROTOCOL => ['resource' => $resource]]
        );
        $stream->context = $context;
        $this->assertTrue($stream->open());
        $this->assertSame($resource, $stream->getResource());
    }

    public function testOpenStreamReturnsFalseOnFailed()
    {
        $stream  = new Stream(false);
        $context = stream_context_create(
            [Stream::PROTOCOL => ['resource' => false]]
        );
        $stream->context = $context;
        $this->assertFalse($stream->open());
        $this->assertNull($stream->getResource());
    }

    public function testFopenFromReference()
    {
        $stream = Stream::fopen('php://memory');
        $this->assertTrue(is_resource($stream));

        $metadata = stream_get_meta_data($stream);
        $wrapper  = $metadata['wrapper_data'];
        $this->assertInstanceOf('Es\Http\AbstractStream', $wrapper);
        $uri = $metadata['uri'];
        $this->assertEquals($uri, Stream::PROTOCOL . '://');
    }

    public function testFopenFromResource()
    {
        $resource = fopen('php://temp', 'r+');
        $stream   = Stream::fopen($resource);
        $this->assertTrue(is_resource($stream));

        $metadata = stream_get_meta_data($stream);
        $wrapper  = $metadata['wrapper_data'];
        $this->assertInstanceOf('Es\Http\AbstractStream', $wrapper);
        $uri = $metadata['uri'];
        $this->assertEquals($uri, Stream::PROTOCOL . '://');
    }

    public function testFopenSetMode()
    {
        $mode   = 'r+';
        $stream = Stream::fopen('php://memory', $mode);
        $this->assertTrue(is_resource($stream));

        $metadata = stream_get_meta_data($stream);
        $this->assertEquals($mode, $metadata['mode']);
    }

    public function testFopenThrowsExceptionWhenResourceIsInvalid()
    {
        $this->setExpectedException('InvalidArgumentException');
        Stream::fopen(false);
    }

    public function testRegister()
    {
        $this->assertTrue(Stream::register());
        $this->assertTrue(in_array(Stream::PROTOCOL, stream_get_wrappers()));
    }

    public function testUnregister()
    {
        $this->assertTrue(Stream::register());
        $this->assertTrue(in_array(Stream::PROTOCOL, stream_get_wrappers()));

        $this->assertTrue(Stream::unregister());
        $this->assertFalse(in_array(Stream::PROTOCOL, stream_get_wrappers()));
    }

    public function testIsRegistered()
    {
        if (in_array(Stream::PROTOCOL, stream_get_wrappers())) {
            Stream::unregister();
        }
        $this->assertFalse(in_array(Stream::PROTOCOL, stream_get_wrappers()));
        $this->assertFalse(Stream::isRegistered());
        //
        Stream::register();
        $this->assertTrue(in_array(Stream::PROTOCOL, stream_get_wrappers()));
        $this->assertTrue(Stream::isRegistered());
    }

    public function testCopyRaiseExceptionIfNoResourceAvailable()
    {
        $stream = new Stream(false);
        $this->setExpectedException('RuntimeException');
        $stream->copy('php://temp');
    }

    public function invalidSourcesToCopyDataProvider()
    {
        $sources = [
            true,
            false,
            'foo',
            [],
            new \stdClass(),
        ];
        $return = [];
        foreach ($sources as $source) {
            $return[] = [$source];
        }

        return $return;
    }

    /**
     * @dataProvider invalidSourcesToCopyDataProvider
     */
    public function testCopyRaiseExceptionIfInvalidSourceProvided($source)
    {
        $stream = new Stream();
        $this->setExpectedException('InvalidArgumentException');
        $stream->copy($source);
    }

    public function testCopyRaiseExceptionIfSourceIsNotReadable()
    {
        $source = Stream::fopen('php://temp', 'w');
        $stream = new Stream();
        $this->setExpectedException('InvalidArgumentException');
        $stream->copy($source);
    }

    public function testCopyCopiesContentFromAbstractStream()
    {
        $content = 'Lorem ipsum dolor sit amet';
        $source  = Stream::make($content);
        $stream  = new Stream();
        $stream->copy($source);
        $this->assertSame($content, $stream->getContents());
    }

    public function testCopyCopiesContentFromResource()
    {
        $content  = 'Lorem ipsum dolor sit amet';
        $resource = fopen('php://temp', 'w+b');
        fwrite($resource, $content);
        $stream = new Stream();
        $stream->copy($resource);
        $this->assertSame($content, $stream->getContents());
    }

    public function testCopyCopiesContentUsingPath()
    {
        $content = 'Lorem ipsum dolor sit amet';
        $temp    = sys_get_temp_dir();
        $path    = $temp . PHP_DS . 'foo.bar';
        $fp      = fopen($path, 'w+b');
        fwrite($fp, $content);
        fclose($fp);

        $stream = new Stream();
        $stream->copy($path);
        $this->assertSame($content, $stream->getContents());
        unlink($path);
    }

    public function testSetResourceFromResource()
    {
        $resource = fopen('php://memory', 'w+b');
        $stream   = new Stream(false);
        $stream->setResource($resource);
        $this->assertSame($resource, $stream->getResource());
    }

    public function testSetResourceFromPathReference()
    {
        $stream = new Stream(false);
        $stream->setResource('php://memory');
        $resource = $stream->getResource();
        $metadata = stream_get_meta_data($resource);
        $this->assertEquals('php://memory', $metadata['uri']);
    }

    public function testSetResourceThrowsExceptionWhenInvalidPathProvided()
    {
        $stream = new Stream(false);
        $this->setExpectedException('InvalidArgumentException');
        $stream->setResource('fail://');
    }

    public function testSetResourceThrowsExceptionWhenInvalidResourceProvided()
    {
        $stream = new Stream(false);
        $this->setExpectedException('InvalidArgumentException');
        $stream->setResource(false);
    }

    public function testSetResourceThrowsExceptionWhenResourceAlreadySet()
    {
        $stream = new Stream(false);
        $stream->setResource('php://temp');
        $this->setExpectedException('DomainException');
        $stream->setResource('php://memory');
    }

    public function testGetResource()
    {
        $stream = new Stream(false);
        $this->assertNull($stream->getResource());
        //
        $resource = fopen('php://memory', 'w+b');
        $stream->setResource($resource);
        $this->assertSame($resource, $stream->getResource());
    }

    public function testStat()
    {
        $resource = fopen('php://memory', 'w+b');
        $stream   = new Stream(false);
        $stream->setResource($resource);
        $this->assertEquals(fstat($resource), $stream->stat());
    }

    public function testFlush()
    {
        $stream = new Stream(false);
        $this->assertTrue($stream->flush());
    }

    public function testCall()
    {
        $stream = new Stream(false);
        $stream->setResource('php://memory');

        $this->assertEquals($stream->stat(),  $stream->stream_stat());
        $this->assertEquals($stream->flush(), $stream->stream_flush());
        $this->assertEquals($stream->open(),  $stream->stream_open());
    }

    public function testCallThrowsExceptionWhenMethodNotExists()
    {
        $stream = new Stream(false);
        $this->setExpectedException('InvalidArgumentException');
        $stream->stream_not_exists_method();
    }
}
