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
use RuntimeException;

/**
 * The emitter of response.
 */
class SapiEmitter implements EmitterInterface
{
    /**
     * Emits the response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response The response
     *
     * @throws \RuntimeException If headers is already sent
     */
    public function emit(ResponseInterface $response)
    {
        $filename = $linenum = null;
        if (headers_sent($filename, $linenum)) {
            throw new RuntimeException(sprintf(
                'Unable to emit response; headers already sent in "%s" '
                . 'on line "%s".',
                $filename,
                $linenum
            ));
        }
        $this
            ->emitStatusLine($response)
            ->emitHeaders($response)
            ->emitBody($response);
    }

    /**
     * Emits the status line.
     *
     * @param \Psr\Http\Message\ResponseInterface $response The response
     *
     * @return self
     */
    protected function emitStatusLine(ResponseInterface $response)
    {
        $reasonPhrase = $response->getReasonPhrase();
        header(sprintf(
            'HTTP/%s %d%s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            ($reasonPhrase ? ' ' . $reasonPhrase : '')
        ));

        return $this;
    }

    /**
     * Emits headers.
     *
     * @param \Psr\Http\Message\ResponseInterface $response The response
     *
     * @return self
     */
    protected function emitHeaders(ResponseInterface $response)
    {
        $normalize = function ($headerName) {
            $name     = str_replace('-', ' ', $headerName);
            $filtered = str_replace(' ', '-', ucwords($name));

            return $filtered;
        };
        foreach ($response->getHeaders() as $headerName => $values) {
            $name  = $normalize($headerName);
            $first = true;
            foreach ($values as $value) {
                header(sprintf(
                    '%s: %s',
                    $name,
                    $value
                ), $first);
                $first = false;
            }
        }

        return $this;
    }

    /**
     * Emits body.
     *
     * @param \Psr\Http\Message\ResponseInterface $response The response
     *
     * @return self
     */
    protected function emitBody(ResponseInterface $response)
    {
        while (ob_get_level()) {
            ob_end_flush();
        }

        $body = $response->getBody();
        if ($body->getSize()) {
            echo $body;
        }

        return $this;
    }
}
