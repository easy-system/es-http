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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Representation of an incoming, server-side HTTP request.
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * The server parameters.
     *
     * @var array
     */
    protected $serverParams = [];

    /**
     * The coockies.
     *
     * @var array
     */
    protected $cookieParams = [];

    /**
     * The query parameters.
     *
     * @var array
     */
    protected $queryParams = [];

    /**
     * The uploaded files.
     *
     * @var array
     */
    protected $uploadedFiles = [];

    /**
     * The attributes of current request.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The parsed body.
     *
     * @var array
     */
    protected $parsedBody = [];

    /**
     * Constructor.
     *
     * @param null|array $serverParams  Optional; the server parameters
     * @param null|array $cookieParams  Optional; the cookies, if any
     * @param null|array $queryParams   Optional; the query parameters, if any
     * @param null|array $uploadedFiles Optional; the uploaded files, if any
     * @param null|array $attributes    Optional; the application-specified attributes
     *
     * <!-- -->
     * @param null|array|object $parsedBody Optional; the parsed body
     *
     * <!-- -->
     * @param null|string|resource|\Psr\Http\Message\StreamInterface $body Optional; the body
     *
     * <!-- -->
     * @param null|array $headers Optional; the headers
     *
     * <!-- -->
     * @param null|string|\Psr\Http\Message\UriInterface $uri Optional; the URI
     *
     * <!-- -->
     * @param null|string $method   Optional; the request method
     * @param null|string $protocol Optional; the HTTP protocol version
     */
    public function __construct(
        array $serverParams = null,
        array $cookieParams = null,
        array $queryParams = null,
        array $uploadedFiles = null,
        array $attributes = null,
        $parsedBody = null,
        $body = null,
        array $headers = null,
        $uri = null,
        $method = null,
        $protocol = null
    ) {
        $this->serverParams = (array) $serverParams;
        $this->cookieParams = (array) $cookieParams;
        $this->queryParams  = (array) $queryParams;
        $this->attributes   = (array) $attributes;

        $this
            ->setParsedBody($parsedBody)
            ->setUploadedFiles((array) $uploadedFiles);

        if (null === $body) {
            $body = 'php://input';
        }
        parent::__construct($body, $headers, $uri, $method, $protocol);
    }

    /**
     * Retrieve server parameters.
     *
     * Retrieves data related to the incoming request environment,
     * typically derived from PHP's $_SERVER superglobal. The data IS NOT
     * REQUIRED to originate from $_SERVER.
     *
     * @return array The server parameters
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Gets the server parameter.
     *
     * @param string $name    The name of parameter
     * @param mixed  $default The default value
     *
     * @return mixed Returns the parameter value if parameter exists,
     *               $default otherwise
     */
    public function getServerParam($name, $default = null)
    {
        if (isset($this->serverParams[$name])) {
            return $this->serverParams[$name];
        }

        return $default;
    }

    /**
     * Retrieve cookies.
     *
     * Retrieves cookies sent by the client to the server.
     *
     * The data MUST be compatible with the structure of the $_COOKIE
     * superglobal.
     *
     * @return array The cookies
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * Gets the cookie parameter.
     *
     * @param string $name    The name of parameter
     * @param mixed  $default The default value
     *
     * @return mixed Returns the parameter value if parameter exists,
     *               $default otherwise
     */
    public function getCookieParam($name, $default = null)
    {
        if (isset($this->cookieParams[$name])) {
            return $this->cookieParams[$name];
        }

        return $default;
    }

    /**
     * Return an instance with the specified cookies.
     *
     * The data IS NOT REQUIRED to come from the $_COOKIE superglobal, but MUST
     * be compatible with the structure of $_COOKIE. Typically, this data will
     * be injected at instantiation.
     *
     * @param array $cookies Array of key/value pairs representing cookies
     *
     * @return self
     */
    public function withCookieParams(array $cookies)
    {
        $new = clone $this;

        $new->cookieParams = $cookies;

        return $new;
    }

    /**
     * Retrieve query string arguments.
     *
     * @return array The query parameters
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * Gets the query parameter.
     *
     * @param string $name    The name of parameter
     * @param mixed  $default The default value
     *
     * @return mixed Returns the parameter value if parameter exists,
     *               $default otherwise
     */
    public function getQueryParam($name, $default = null)
    {
        if (isset($this->queryParams[$name])) {
            return $this->queryParams[$name];
        }

        return $default;
    }

    /**
     * Return an instance with the specified query string arguments.
     *
     * @param array $query Array of query string arguments, typically from $_GET
     *
     * @return self
     */
    public function withQueryParams(array $query)
    {
        $new = clone $this;

        $new->queryParams = $query;

        return $new;
    }

    /**
     * Retrieve normalized file upload data.
     *
     * This method returns upload metadata in a normalized tree, with each leaf
     * an instance of Psr\Http\Message\UploadedFileInterface
     *
     * @return array An array tree of UploadedFileInterface instances; an empty
     *               array MUST be returned if no data is present
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * Create a new instance with the specified uploaded files.
     *
     * @param array An array tree of UploadedFileInterface instances
     *
     * @return self
     */
    public function withUploadedFiles(array $files)
    {
        $new = clone $this;

        $new->setUploadedFiles($files);

        return $new;
    }

    /**
     * Retrieve any parameters provided in the request body.
     *
     * @return null|array|object The deserialized body parameters, if any
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * Return an instance with the specified body parameters.
     *
     * @param null|array|object $data The deserialized body data. This will
     *                                typically be in an array or object
     *
     * @return self
     */
    public function withParsedBody($data)
    {
        $new = clone $this;

        $new->setParsedBody($data);

        return $new;
    }

    /**
     * Retrieve attributes derived from the request.
     *
     * @return array Attributes derived from the request
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Retrieve a single derived request attribute.
     *
     * This method obviates the need for a hasAttribute() method, as it allows
     * specifying a default value to return if the attribute is not found.
     *
     * @see getAttributes()
     *
     * @param string $name    The attribute name
     * @param mixed  $default Default value to return if the attribute does
     *                        not exist
     *
     * @return mixed
     */
    public function getAttribute($name, $default = null)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return $default;
    }

    /**
     * Return an instance with the specified derived request attribute.
     *
     * @see getAttributes()
     *
     * @param string $name  The attribute name
     * @param mixed  $value The value of the attribute
     *
     * @return self
     */
    public function withAttribute($name, $value)
    {
        $new = clone $this;

        $new->attributes[$name] = $value;

        return $new;
    }

    /**
     * Return an instance with the specified attributes.
     *
     * @param array $attributes The attributes
     *
     * @return self
     */
    public function withAttributes(array $attributes)
    {
        $new = clone $this;

        $new->attributes = $attributes;

        return $new;
    }

    /**
     * Return an instance with the specified attributes appended with the
     * existed attributes.
     *
     * @param array $attributes The attributes
     *
     * @return self
     */
    public function withAddedAttributes(array $attributes)
    {
        $new = clone $this;

        $new->attributes = array_merge($this->attributes, $attributes);

        return $new;
    }

    /**
     * Return an instance that removes the specified derived request attribute.
     *
     * @see getAttributes()
     *
     * @param string $name The attribute name
     *
     * @return self
     */
    public function withoutAttribute($name)
    {
        $new = clone $this;
        if (array_key_exists($name, $new->attributes)) {
            unset($new->attributes[$name]);
        }

        return $new;
    }

    /**
     * Sets the uploaded files.
     *
     * @param array $files The uploaded files
     *
     * @throws \InvalidArgumentException If invalid structure is provided
     *
     * @return self
     */
    protected function setUploadedFiles(array $files)
    {
        $validate = function ($files) use (&$validate) {
            foreach ($files as $item) {
                if (is_array($item)) {
                    $validate($item);
                    continue;
                }
                if (! $item instanceof UploadedFileInterface) {
                    throw new InvalidArgumentException(
                        'Invalid structure of uploaded files provided.'
                    );
                }
            }
        };
        $validate($files);
        $this->uploadedFiles = $files;

        return $this;
    }

    /**
     * Sets the parsed body.
     *
     * @param null|array|object $data The parsed body
     *
     * @throws \InvalidArgumentException If an unsupported argument type is
     *                                   provided
     *
     * @return self
     */
    protected function setParsedBody($data)
    {
        if (! is_null($data) && ! is_array($data) && ! is_object($data)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid parsed body provided; must be an null, or array, '
                . 'or object, "%s" provided.',
                gettype($data)
            ));
        }
        $this->parsedBody = $data;

        return $this;
    }

    /**
     * Sets the body.
     *
     * @param string|resource|\Psr\Http\Message\StreamInterface $body The body
     *
     * @throws \InvalidArgumentException If invalid body is provided
     *
     * @return self
     */
    protected function setBody($body = 'php://input')
    {
        if (is_string($body) || is_resource($body)) {
            $body = new Stream($body, 'r');
        }
        if (! $body instanceof StreamInterface) {
            throw new InvalidArgumentException(sprintf(
                'Invalid body provided. Must be a string resource identifier, or resource, '
                . 'or instance of Psr\Http\Message\StreamInterface; received "%s".',
                is_object($body) ? get_class($body) : gettype($body)
            ));
        }
        $this->setBodyInstance($body);

        return $this;
    }
}
