<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * Representation of the response emitter.
 */
interface EmitterInterface
{
    /**
     * Emits the response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response The response
     *
     * @throws \RuntimeException If unable to emit the response
     */
    public function emit(ResponseInterface $response);
}
