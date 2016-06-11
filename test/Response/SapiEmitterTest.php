<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http\Test\Response;

use Es\Http\Response\SapiEmitter;
use Es\Http\Response;

class SapiEmitterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once 'FakeSapi.php';
    }

    public function testEmitStatusLine()
    {
        $response = new Response(
            200,
            null,
            null,
            '1.0'
        );
        Response\send_headers(false);
        $emitter = new SapiEmitter();
        $emitter->emit($response);
        $this->assertContains('HTTP/1.0 200 OK', Response\get_headers());
    }

    public function testEmitHeaders()
    {
        $response = new Response(
            200,
            null,
            ['Content-Type' => 'text/plain'],
            '1.0'
        );
        Response\send_headers(false);
        $emitter = new SapiEmitter();
        $emitter->emit($response);
        $this->assertContains('Content-Type: text/plain', Response\get_headers());
    }

    public function testEmitBody()
    {
        $response = new Response();
        $content = 'Lorem ipsum dolor sit amet';
        $response->getBody()->write($content);

        Response\send_headers(false);
        $emitter = new SapiEmitter();
        ob_start();
        $emitter->emit($response);
        $buffer = ob_get_clean();
        $this->assertSame($content, $buffer);
        $this->assertSame(0, Response\ob_get_level());
    }

    public function testEmitRaiseExceptionIfHeadersAlreadySent()
    {
        $response = new Response();
        Response\send_headers(true);
        $emitter = new SapiEmitter();
        $this->setExpectedException('RuntimeException');
        $emitter->emit($response);
    }
}
