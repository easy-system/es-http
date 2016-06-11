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

use Es\Http\Message;
use Es\Http\Stream;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $stream   = new Stream();
        $headers  = ['Content-Type' => ['text/html;charset=utf-8']];
        $protocol = '1.0';
        $message  = new Message($stream, $headers, $protocol);
        $this->assertSame($stream, $message->getBody());
        $this->assertSame($headers, $message->getHeaders());
        $this->assertSame($protocol, $message->getProtocolVersion());
    }

    public function testWithProtocolVersionOnSuccess()
    {
        $message = new Message();

        $new = $message->withProtocolVersion('1.0');
        $this->assertNotSame($new, $message);
        $this->assertEquals($new->getProtocolVersion(), '1.0');
    }

    public function invalidProtocolVersionProvider()
    {
        $config = [
            '  ',
            1.0,
            [],
            new \stdClass(),
            false,
            '0.9',
            '2.0',
        ];
        $return = [];
        foreach ($config as $value) {
            $return[] = [$value];
        }

        return $return;
    }

    /**
     * @dataProvider invalidProtocolVersionProvider
     */
    public function testWithProtocolVersionThrowsExceptionWithInvalidArguments($version)
    {
        $this->setExpectedException('DomainException');
        $message = new Message();
        $message->withProtocolVersion($version);
    }

    public function testGetProtocolVersionByDefault()
    {
        $message = new Message();
        $this->assertEquals('1.1', $message->getProtocolVersion());
    }

    public function testWithHeaderReturnsNewInstance()
    {
        $message = new Message();

        $new = $message->withHeader('foo', 'bar');
        $this->assertNotSame($new, $message);
    }

    public function testWithHeaderSetHeader()
    {
        $message = new Message();

        $new = $message->withHeader('foo', ['bar', 'baz']);
        $this->assertEquals($new->getHeader('foo'), ['bar', 'baz']);
    }

    public function invalidHeadersProvider()
    {
        $config = [
            // invalid header values, not array
            'foo' => false,
            'bar' => 10,
            'ban' => '   ',
            'bat' => chr(27),
            // invalid header values, invalid item
            'coc' => [true],
            'cod' => [false],
            'cor' => [[]],
            'cot' => [new \stdClass()],

            // invalid header keys
            'car№4' => 'something',
            false   => 'something',
            12      => 'something',
            '     ' => 'something',
        ];
        $return = [];
        foreach ($config as $key => $value) {
            $return[] = [$key, $value];
        }

        return $return;
    }

    /**
     * @dataProvider invalidHeadersProvider
     */
    public function testWithHeaderThrowsExceptionOnInvalidHeaderProvided($headerName, $headerValue)
    {
        $this->setExpectedException('InvalidArgumentException');
        $message = new Message();
        $message->withHeader($headerName, $headerValue);
    }

    public function testWithHeaderNormalizeWhitespaces()
    {
        $message = new Message();

        $new = $message->withHeader('X-Pangram', 'Lorem  ipsum  dolor  sit   amet');
        $this->assertEquals($new->getHeader('X-Pangram'), ['Lorem ipsum dolor sit amet']);
    }

    public function testWithHeaderNormalizeMultilineValue()
    {
        $message = new Message();

        $new = $message->withHeader('X-Pangram', "Lorem ipsum dolor \n\t sit amet");
        $this->assertEquals($new->getHeader('X-Pangram'), ['Lorem ipsum dolor sit amet']);
    }

    public function testWithHeaderRemoveLeadingAndTrailingWhitespacesFromValue()
    {
        $message = new Message();

        $new = $message->withHeader('X-Pangram', ' Lorem ipsum dolor sit amet ');
        $this->assertEquals($new->getHeader('X-Pangram'), ['Lorem ipsum dolor sit amet']);
    }

    public function testWithHeaderRemoveLeadingAndTrailingWhitespacesFromName()
    {
        $message = new Message();

        $new = $message->withHeader(' X-Pangram ', 'Lorem ipsum dolor sit amet');
        $this->assertEquals($new->getHeader('X-Pangram'), ['Lorem ipsum dolor sit amet']);
    }

    public function testWithHeaderReplaceOldValueWithNewValue()
    {
        $message = new Message('php://temp', ['foo' => 'bar']);

        $new = $message->withHeader('foo', 'bat');
        $this->assertEquals($new->getHeader('foo'), ['bat']);
    }

    public function testWithHeaderCaseInsensitive()
    {
        $message = new Message('php://temp', ['foo' => 'bar']);

        $new = $message->withHeader('FOO', 'bat');
        $this->assertEquals($new->getHeader('foo'), ['bat']);
    }

    public function testWithAddedHeaderReturnsNewInstance()
    {
        $message = new Message();

        $new = $message->withAddedHeader('foo', 'bar');
        $this->assertNotSame($message, $new);
    }

    public function testWithAddedHeaderSetValue()
    {
        $message = new Message();

        $new = $message->withAddedHeader('foo', ['bar', 'baz']);
        $this->assertEquals(['bar', 'baz'], $new->getHeader('foo'));
    }

    public function testWithAddedHeaderAddValues()
    {
        $message = new Message('php://temp', ['foo' => 'bar']);

        $new = $message->withAddedHeader('foo', ['ban', 'bat']);
        $this->assertEquals(['bar', 'ban', 'bat'], $new->getHeader('foo'));
    }

    /**
     * @dataProvider invalidHeadersProvider
     */
    public function testWithAddedHeaderThrowsExceptionOnInvalidHeaderProvided($headerName, $headerValue)
    {
        $this->setExpectedException('InvalidArgumentException');
        $message = new Message();
        $message->withAddedHeader($headerName, $headerValue);
    }

    public function testWithAddedHeaderNormalizeWhitespaces()
    {
        $message = new Message();

        $new = $message->withAddedHeader('X-Pangram', 'Lorem  ipsum  dolor  sit   amet');
        $this->assertEquals($new->getHeader('X-Pangram'), ['Lorem ipsum dolor sit amet']);
    }

    public function testWithAddedHeaderNormalizeMultilineValue()
    {
        $message = new Message();

        $new = $message->withAddedHeader('X-Pangram', "Lorem ipsum dolor \n\t sit amet");
        $this->assertEquals($new->getHeader('X-Pangram'), ['Lorem ipsum dolor sit amet']);
    }

    public function testWithAddedHeaderRemoveLeadingAndTrailingWhitespacesFromName()
    {
        $message = new Message();

        $new = $message->withAddedHeader(' X-Pangram ', 'Lorem ipsum dolor sit amet');
        $this->assertEquals($new->getHeader('X-Pangram'), ['Lorem ipsum dolor sit amet']);
    }

    public function testWithAddedHeaderRemoveLeadingAndTrailingWhitespacesFromValue()
    {
        $message = new Message();

        $new = $message->withAddedHeader('X-Pangram', ' Lorem ipsum dolor sit amet ');
        $this->assertEquals($new->getHeader('X-Pangram'), ['Lorem ipsum dolor sit amet']);
    }

    public function testWithAddedHeaderCaseInsensitive()
    {
        $message = new Message('php://temp', ['foo' => 'bar']);

        $new = $message->withAddedHeader('FOO', ['ban', 'bat']);
        $this->assertEquals(['bar', 'ban', 'bat'], $new->getHeader('foo'));
    }

    public function testWithoutHeaderReturnsNewInstance()
    {
        $message = new Message();

        $new = $message->withoutHeader('foo');
        $this->assertNotSame($message, $new);
    }

    public function testWithoutHeaderRemoveHeader()
    {
        $message = new Message('php://temp', ['foo' => 'bar']);

        $new = $message->withoutHeader('foo');
        $this->assertEquals([], $new->getHeader('foo'));
    }

    public function testWithoutHeaderCaseInsensitive()
    {
        $message = new Message('php://temp', ['foo' => 'bar']);

        $new = $message->withoutHeader('foo');
        $this->assertEquals([], $new->getHeader('FOO'));
    }

    public function testHasHeaderReturnsTrueOnSuccess()
    {
        $message = new Message('php://temp', ['foo' => 'bar']);
        $this->assertTrue($message->hasHeader('foo'));
    }

    public function testHasHeaderReturnsFaseOnFail()
    {
        $message = new Message();
        $this->assertFalse($message->hasHeader('foo'));
    }

    public function testHasHeaderCaseInsensitive()
    {
        $message = new Message('php://temp', ['foo' => 'bar']);
        $this->assertTrue($message->hasHeader('FOO'));
    }

    public function testHasHeaderIgnoreLeadingAndTrailingWhitespaces()
    {
        $message = new Message('php://temp', ['foo' => 'bar']);
        $this->assertTrue($message->hasHeader(' foo '));
    }

    public function testGetHeadersReturnHeaders()
    {
        $headers = [
            'Foo' => ['Bar', 'Baz'],
            'Cap' => ['Con', 'Cop'],
        ];
        $message = new Message('php://temp', $headers);
        $this->assertEquals($headers, $message->getHeaders());
    }

    public function testGetHeadersReturnEmptyArrayWhenHeadersNotSpecified()
    {
        $message = new Message();
        $this->assertEquals([], $message->getHeaders());
    }

    public function testGetHeaderReturnsHeader()
    {
        $message = new Message('php://temp', ['foo' => ['baz', 'bat']]);
        $this->assertEquals(['baz', 'bat'], $message->getHeader('foo'));
    }

    public function testGetHeaderReturnsEmptyArrayWhenHeaderNotSpecified()
    {
        $message = new Message();
        $this->assertEquals([], $message->getHeader('foo'));
    }

    public function testGetHeaderCaseInsensitive()
    {
        $message = new Message('php://temp', ['foo' => ['baz', 'bat']]);
        $this->assertEquals(['baz', 'bat'], $message->getHeader('FOO'));
    }

    public function testGetHeaderIgnoreLeadingAndTrailingWhitespaces()
    {
        $message = new Message('php://temp', ['foo' => ['baz', 'bat']]);
        $this->assertEquals(['baz', 'bat'], $message->getHeader(' foo '));
    }

    public function testGetHeaderLineReturnsHeaderLine()
    {
        $message = new Message('php://temp', ['foo' => ['baz', 'bat']]);
        $this->assertEquals('baz,bat', $message->getHeaderLine('foo'));
    }

    public function testGetHeaderLineReturnsEmptyLineWhenHeaderNotSpecified()
    {
        $message = new Message();
        $this->assertEquals('', $message->getHeaderLine('foo'));
    }

    public function testWithBodyReturnsNewInstance()
    {
        $message = new Message();

        $body = new Stream();
        $new  = $message->withBody($body);
        $this->assertNotSame($message, $new);
    }

    public function testWithBodySetBody()
    {
        $message = new Message();

        $body = new Stream();
        $new  = $message->withBody($body);
        $this->assertSame($body, $new->getBody());
    }

    public function invalidBodyDataProvider()
    {
        return [
            [true],
            [false],
            [100],
            [[]],
            [new \stdClass()],
        ];
    }

    /**
     * @dataProvider invalidBodyDataProvider
     */
    public function testWithBodyRaiseExceptionIfInvalidBodyProvided($body)
    {
        $this->setExpectedException('InvalidArgumentException');
        $message = new Message($body);
    }
}
