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

use Es\Http\Response\EmitterInterface;

/**
 * The representation of the basic access point to HTTP layer.
 */
interface ServerInterface
{
    /**
     * Sets the request.
     *
     * @param ServerRequest The request
     *
     * @return self
     */
    public function setRequest(ServerRequest $request);

    /**
     * Gets the request.
     *
     * @param bool $master Optional; true by default. True to returns the
     *                     master request, false means new request
     *
     * @return ServerRequest The request
     */
    public function getRequest($master = true);

    /**
     * Sets the response.
     *
     * @param Response $response The response
     *
     * @return self
     */
    public function setResponse(Response $response);

    /**
     * Gets the response.
     *
     * @param bool $master Optional; true by default. True to returns the
     *                     master response, false means new response
     *
     * @return Response The response
     */
    public function getResponse($master = true);

    /**
     * Sets the emitter.
     *
     * @param \Es\Http\Response\EmitterInterface $emitter The emitter
     *
     * @return self
     */
    public function setEmitter(EmitterInterface $emitter);

    /**
     * Gets the emitter.
     *
     * @return \Es\Http\Response\EmitterInterface The emitter
     */
    public function getEmitter();
}
