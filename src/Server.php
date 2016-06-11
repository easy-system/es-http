<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http;

use Es\Http\Factory\ServerRequestFactory;
use Es\Http\Response\EmitterInterface;
use Es\Http\Response\SapiEmitter;

/**
 * The basic access point to HTTP layer.
 */
class Server implements ServerInterface
{
    /**
     * The request.
     *
     * @var ServerRequest
     */
    protected $request;

    /**
     * The response.
     *
     * @var Response
     */
    protected $response;

    /**
     * The emmiter.
     *
     * @var \Es\Http\Response\EmitterInterface
     */
    protected $emitter;

    /**
     * Constructor.
     *
     * @param ServerRequest                      $request  The request
     * @param Response                           $response The response
     * @param \Es\Http\Response\EmitterInterface $emitter  The emitter
     */
    public function __construct(
        ServerRequest $request = null,
        Response $response = null,
        EmitterInterface $emitter = null
    ) {
        if ($request) {
            $this->setRequest($request);
        }
        if ($response) {
            $this->setResponse($response);
        }
        if ($emitter) {
            $this->setEmitter($emitter);
        }
    }

    /**
     * Sets the request.
     *
     * @param ServerRequest The request
     *
     * @return self
     */
    public function setRequest(ServerRequest $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Gets the request.
     *
     * @param bool $master Optional; true by default. True to returns the
     *                     master request, false means new request
     *
     * @return ServerRequest The request
     */
    public function getRequest($master = true)
    {
        if (! $master) {
            return ServerRequestFactory::make();
        }
        if (! $this->request) {
            $this->request = ServerRequestFactory::make();
        }

        return $this->request;
    }

    /**
     * Sets the response.
     *
     * @param Response $response The response
     *
     * @return self
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Gets the response.
     *
     * @param bool $master Optional; true by default. True to returns the
     *                     master response, false means new response
     *
     * @return Response The response
     */
    public function getResponse($master = true)
    {
        if (! $master) {
            return new Response();
        }
        if (! $this->response) {
            $this->response = new Response();
        }

        return $this->response;
    }

    /**
     * Sets the emitter.
     *
     * @param \Es\Http\Response\EmitterInterface $emitter The emitter
     *
     * @return self
     */
    public function setEmitter(EmitterInterface $emitter)
    {
        $this->emitter = $emitter;

        return $this;
    }

    /**
     * Gets the emitter.
     *
     * @return \Es\Http\Response\EmitterInterface The emitter
     */
    public function getEmitter()
    {
        if (! $this->emitter) {
            $this->emitter = new SapiEmitter();
        }

        return $this->emitter;
    }
}
