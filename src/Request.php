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

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Representation of an outgoing, client-side request.
 */
class Request extends Message implements RequestInterface
{
    /**#@+
     * The REST methods.
     *
     * @const string
     */
    const METHOD_CONNECT = 'CONNECT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_GET     = 'GET';
    const METHOD_HEAD    = 'HEAD';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_PATH    = 'PATH';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_TRACE   = 'TRACE';
    /**#@-*/

    /**
     * The representation of requested URI.
     *
     * @var \Psr\Http\Message\UriInterface
     */
    protected $uri;

    /**
     * The request-target, if it has been provided or calculated.
     *
     * @var null|string
     */
    protected $target;

    /**
     * The HTTP method of the request.
     *
     * @var string
     */
    protected $method = 'GET';

    /**
     * Standardized HTTP methods.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-4.1
     *
     * @var array
     */
    protected $restMethods = [
        self::METHOD_CONNECT,
        self::METHOD_DELETE,
        self::METHOD_GET,
        self::METHOD_HEAD,
        self::METHOD_OPTIONS,
        self::METHOD_PATH,
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_TRACE,
    ];

    /**
     * Constructor.
     *
     * @param null|string|resource|\Psr\Http\Message\StreamInterface $body Optional;
     *
     * null by default means "php://temp"
     * The body of request:
     * - the string as reference to resource as "scheme://target"
     * - or the resource to wrap
     * - or instance of stream
     * @param null|array                                 $headers  Optional; the request headers
     * @param null|string|\Psr\Http\Message\UriInterface $uri      Optional; the requested URI
     * @param null|string                                $method   Optional; null by default means
     *                                                             "GET". The request method
     * @param null|string                                $protocol Optional; null by default means
     *                                                             "1.1". The HTTP protocol version
     */
    public function __construct(
        $body = null,
        array $headers = null,
        $uri = null,
        $method = null,
        $protocol = null
    ) {
        parent::__construct($body, $headers, $protocol);

        // set uri after headers and preserve host
        if (null === $uri) {
            $uri = '';
        }
        $this->setUri($uri, true);
        if (null !== $method) {
            $this->setMethod($method);
        }
    }

    /**
     * Return an instance with the specific request-target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-5.3 (for the various
     *     request-target forms allowed in request messages)
     *
     * @param string|\Psr\Http\Message\UriInterface $target The target
     *
     * @return self
     */
    public function withRequestTarget($target)
    {
        $new = clone $this;
        $new->setTarget($target);

        return $new;
    }

    /**
     * Retrieves the message's request target.
     *
     * @return string The target
     */
    public function getRequestTarget()
    {
        if ($this->target) {
            return $this->target;
        }
        $uri    = $this->getUri();
        $target = $uri->getPath();
        if (! $target) {
            $target = '/';
        }
        $query = $uri->getQuery();
        if ($query) {
            $target .= '?' . $query;
        }

        return $target;
    }

    /**
     * Return an instance with the provided HTTP method.
     * Standard REST methods will be changed to uppercase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-4.1
     *
     * @param string $method The request method, case-sensetive for
     *                       resource-specific methods
     *
     * @return self
     */
    public function withMethod($method)
    {
        $new = clone $this;
        $new->setMethod($method);

        return $new;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns an instance with the provided URI.
     *
     * This method MUST update the Host header of the returned request by
     * default if the URI contains a host component. If the URI does not
     * contain a host component, any pre-existing Host header MUST be carried
     * over to the returned request.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @param UriInterface $uri          New request URI to use
     * @param bool         $preserveHost Preserve the original state of the
     *                                   Host header
     *
     * @return self
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $new = clone $this;
        $new->setUri($uri, $preserveHost);

        return $new;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     *
     * @return UriInterface Returns a UriInterface instance
     *                      representing the URI of the request
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Sets the request HTTP target.
     *
     * @param string|\Psr\Http\Message\UriInterface $target The target
     *
     * @throws \InvalidArgumentException If invalid target provided
     *
     * @return self
     */
    protected function setTarget($target)
    {
        if ('*' == $target) {
            $this->target = $target;

            return $this;
        }
        if (is_string($target)) {
            $target = new Uri($target);
        }

        if (! $target instanceof UriInterface) {
            throw new InvalidArgumentException(sprintf(
                'Invalid target provided; must be an string or instance of '
                . '"%s", "%s" received.',
                'Psr\\Http\\Message\\UriInterface',
                is_object($target) ? get_class($target) : gettype($target)
            ));
        }
        $this->target = (string) $target->withFragment('');

        return $this;
    }

    /**
     * Sets the URI.
     *
     * @param string|\Psr\Http\Message\UriInterface $uri The URI
     *
     * If the URI contains a host component, the Host header will update. You
     * can opt-in to preserving the original state of the Host header by
     * setting `$preserveHost` to `true`.
     * @param bool $preserveHost Preserve the original state of the Host header
     *
     * @throws \InvalidArgumentException If Invalid URI provided
     *
     * @return self
     */
    protected function setUri($uri, $preserveHost = false)
    {
        if (is_string($uri)) {
            $uri = new Uri($uri);
        }
        if (! $uri instanceof UriInterface) {
            throw new InvalidArgumentException(sprintf(
                'Invalid URI provided. Must be null, a string, or '
                . 'Psr\Http\Message\UriInterface; received "%s".',
                is_object($uri) ? get_class($uri) : gettype($uri)
            ));
        }
        $this->uri = $uri;

        if ($preserveHost && $this->hasHeader('Host')) {
            return $this;
        }

        if (! $uri->getHost()) {
            return $this;
        }

        $host = $uri->getHost();
        if ($uri->getPort()) {
            $host .= ':' . $uri->getPort();
        }
        $this->setHeader('Host', $host);

        return $this;
    }

    /**
     * Sets the HTTP method.
     *
     * @param string $method The method
     *
     * @throws \InvalidArgumentException If invalid method provided
     *
     * @return self
     */
    protected function setMethod($method)
    {
        if (! is_string($method)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid HTTP method provided. Must be a string; received "%s".',
                is_object($method) ? get_class($method) : gettype($method)
            ));
        }

        $restMethod = strtoupper($method);

        if (in_array($restMethod, $this->restMethods)) {
            $method = $restMethod;
        }
        $this->method = $method;

        return $this;
    }
}
