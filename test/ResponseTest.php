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

use Es\Http\Response;
use Es\Http\Stream;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $status   = '302';
        $body     = new Stream();
        $headers  = ['foo' => ['bar', 'baz']];
        $protocol = '1.0';
        $response = new Response(
            $status,
            $body,
            $headers,
            $protocol
        );
        $this->assertEquals($status,   $response->getStatusCode());
        $this->assertSame($body,       $response->getBody());
        $this->assertEquals($headers,  $response->getHeaders());
        $this->assertEquals($protocol, $response->getProtocolVersion());
    }

    public function testGetStatusCodeReturnsOkCodeByDefault()
    {
        $response = new Response();
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testWithStatusCodeReturnsNewInstance()
    {
        $response = new Response();

        $new = $response->withStatus(302);
        $this->assertNotSame($response, $new);
    }

    public function testWithStatusCodeSetsTheStatusCode()
    {
        $response = new Response();

        $new = $response->withStatus(404);
        $this->assertEquals(404, $new->getStatusCode());
    }

    public function testWithStatusCodeSetsReasonPhrase()
    {
        $response = new Response();

        $new = $response->withStatus(250, 'Lorem ipsum dolor sit amet');
        $this->assertEquals('Lorem ipsum dolor sit amet', $new->getReasonPhrase());
    }

    public function invalidStatusCodeDataProvider()
    {
        $codes = [
            false,
            10,
            10000,
            999,
            '200 Ok',
        ];
        $return = [];
        foreach ($codes as $code) {
            $return[] = [$code];
        }

        return $return;
    }

    /**
     * @dataProvider invalidStatusCodeDataProvider
     */
    public function testInvalidStatusCodeThrowsException($code)
    {
        $response = new Response();
        $this->setExpectedException('InvalidArgumentException');
        $response->withStatus($code);
    }

    public function invalidReasonPhraseProvider()
    {
        $phrases = [
            false,
            null,
            100,
        ];
        $return = [];
        foreach ($phrases as $phrase) {
            $return[] = [$phrase];
        }

        return $return;
    }

    /**
     * @dataProvider invalidReasonPhraseProvider
     */
    public function testInvalidReasonPhraseThrowsException($phrase)
    {
        $response = new Response();
        $this->setExpectedException('InvalidArgumentException');
        $response->withStatus(250, $phrase);
    }

    public function testGetReasonPhraseReturnsStandartReasonPhrase()
    {
        $response = new Response();

        $new = $response->withStatus(102);
        $this->assertEquals('Processing', $new->getReasonPhrase());
    }

    public function testReasonPhraseReturnsEmptyStringIfPhraseNotExists()
    {
        $response = new Response();

        $new = $response->withStatus(250);
        $this->assertEquals('', $new->getReasonPhrase());
    }
}
