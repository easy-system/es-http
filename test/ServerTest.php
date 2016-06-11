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
use Es\Http\Response\SapiEmitter;
use Es\Http\Server;
use Es\Http\ServerRequest;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $request  = new ServerRequest();
        $response = new Response();
        $emitter  = new SapiEmitter();

        $server = new Server($request, $response, $emitter);

        $this->assertSame($request,  $server->getRequest());
        $this->assertSame($response, $server->getResponse());
        $this->assertSame($emitter,  $server->getEmitter());
    }

    public function testSetRequest()
    {
        $request = new ServerRequest();
        $server  = new Server();
        $return  = $server->setRequest($request);
        $this->assertSame($return,  $server);
        $this->assertSame($request, $server->getRequest());
    }

    public function testGetRequestGetsMasterRequest()
    {
        $server = new Server();
        $first  = $server->getRequest();
        $second = $server->getRequest();
        $this->assertSame($first, $second);
        $this->assertInstanceOf('Psr\Http\Message\ServerRequestInterface', $first);
    }

    public function testGetRequestGetsNewRequest()
    {
        $server = new Server();
        $first  = $server->getRequest(false);
        $second = $server->getRequest(false);
        $this->assertNotSame($first, $second);
        $this->assertInstanceof('Psr\Http\Message\ServerRequestInterface', $first);
        $this->assertInstanceof('Psr\Http\Message\ServerRequestInterface', $second);
    }

    public function testSetResponse()
    {
        $response = new Response();
        $server   = new Server();
        $return   = $server->setResponse($response);
        $this->assertSame($return,  $server);
        $this->assertSame($response, $server->getResponse());
    }

    public function testGetResponseGetsMasterResponse()
    {
        $server = new Server();
        $first  = $server->getResponse();
        $second = $server->getResponse();
        $this->assertSame($first, $second);
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $first);
    }

    public function testGetResponseGetsNewResponse()
    {
        $server = new Server();
        $first  = $server->getResponse(false);
        $second = $server->getResponse(false);
        $this->assertNotSame($first, $second);
        $this->assertInstanceof('Psr\Http\Message\ResponseInterface', $first);
        $this->assertInstanceof('Psr\Http\Message\ResponseInterface', $second);
    }

    public function testSetEmitter()
    {
        $emitter = new SapiEmitter();
        $server  = new Server();
        $return  = $server->setEmitter($emitter);
        $this->assertSame($return, $server);
        $this->assertSame($emitter, $server->getEmitter());
    }

    public function testGetEmitter()
    {
        $server = new Server();
        $this->assertInstanceOf('Es\Http\Response\EmitterInterface', $server->getEmitter());
    }
}
