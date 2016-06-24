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

use DomainException;
use Exception;
use InvalidArgumentException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Representation of HTTP message.
 *
 * HTTP messages consist of requests from a client to a server and responses
 * from a server to a client. This interface defines the methods common to
 * each.
 */
class Message implements MessageInterface
{
    /**
     * The body of the message.
     *
     * @var \Psr\Http\Message\StreamInterface
     */
    protected $body;

    /**
     * The registered headers.
     *
     * @var array
     */
    protected $rawHeaders = [];

    /**
     * The map of case-insensitive heder names to raw header names.
     *
     * @var array
     */
    protected $headerNames = [];

    /**
     * The version of HTTP protocol.
     *
     * @var string
     */
    protected $protocol = '1.1';

    /**
     * Constructor.
     *
     * @param null|string|resource|\Psr\Http\Message\StreamInterface $body Optional;
     *
     * null by default means "php://temp"
     * The message body:
     * - the string as reference to resource as "scheme://target"
     * - or the resource to wrap
     * - or instance of stream
     * @param null|array  $headers  Optional; the request headers
     * @param null|string $protocol Optional; null by default means "1.1". The HTTP protocol version
     */
    public function __construct($body = null, array $headers = null, $protocol = null)
    {
        if (null === $body) {
            $body = 'php://temp';
        }
        $this->setBody($body);

        if (null !== $headers) {
            $this->addHeaders($headers);
        }
        if (null !== $protocol) {
            $this->setProtocol($protocol);
        }
    }

    /**
     * Return an instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * @param string $version HTTP protocol version
     *
     * @throws \DomainException If the protocol is not supported
     *
     * @return self
     */
    public function withProtocolVersion($version)
    {
        $new = clone $this;
        try {
            $new->setProtocol($version);
        } catch (Exception $ex) {
            throw new DomainException(sprintf(
                'The protocol "%s" is not supported.',
                is_scalar($version) ? $version : gettype($version)
            ), null, $ex);
        }

        return $new;
    }

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * @return string HTTP protocol version
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    /**
     * Return an instance with the provided value replacing the
     * specified header.
     *
     * While header names are case-insensitive, the casing of the header will
     * be preserved by this function, and returned from getHeaders().
     *
     * @param string          $name  Case-insensitive header field name
     * @param string|string[] $value Header value(s)
     *
     * @throws \InvalidArgumentException For invalid header names or values
     *
     * @return self
     */
    public function withHeader($name, $value)
    {
        $new = clone $this;
        try {
            $new->setHeader($name, $value);
        } catch (InvalidArgumentException $ex) {
            throw new InvalidArgumentException(
                'Invalid header provided.',
                null,
                $ex
            );
        }

        return $new;
    }

    /**
     * Return an instance with the specified header appended with the
     * given value.
     *
     * Existing values for the specified header will be maintained. The new
     * value(s) will be appended to the existing list. If the header did not
     * exist previously, it will be added.
     *
     * @param string          $name  Case-insensitive header field name to add
     * @param string|string[] $value Header value(s)
     *
     * @throws \InvalidArgumentException For invalid header names or values
     *
     * @return self
     */
    public function withAddedHeader($name, $value)
    {
        $new = clone $this;
        try {
            $new->addHeader($name, $value);
        } catch (InvalidArgumentException $ex) {
            throw new InvalidArgumentException(
                'Invalid header provided.',
                null,
                $ex
            );
        }

        return $new;
    }

    /**
     * Return an instance without the specified header.
     *
     * @param string $name Case-insensitive header field name to remove
     *
     * @return self
     */
    public function withoutHeader($name)
    {
        $new = clone $this;
        $new->removeHeader($name);

        return $new;
    }

    /**
     * Checks if a header exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name
     *
     * @return bool Returns true on success, false otherwise
     */
    public function hasHeader($name)
    {
        return isset($this->headerNames[strtolower(trim($name))]);
    }

    /**
     * Retrieves all message header values.
     *
     * @return array Returns an associative array of the message's headers. Each
     *               key MUST be a header name, and each value MUST be an array
     *               of strings for that header
     */
    public function getHeaders()
    {
        return $this->rawHeaders;
    }

    /**
     * Retrieves a message header value by the given case-insensitive name.
     *
     * @param string $name Case-insensitive header field name
     *
     * @return string[] An array of string values as provided for the given
     *                  header. If the header does not appear in the message,
     *                  this method MUST return an empty array
     */
    public function getHeader($name)
    {
        $normalized = strtolower(trim($name));
        if (! isset($this->headerNames[$normalized])) {
            return [];
        }
        $header = $this->headerNames[$normalized];

        return $this->rawHeaders[$header];
    }

    /**
     * Retrieves a comma-separated string of the values for a single header.
     *
     * NOTE: Not all header values may be appropriately represented using
     * comma concatenation. For such headers, use getHeader() instead
     * and supply your own delimiter when concatenating.
     *
     * @param string $name Case-insensitive header field name
     *
     * @return string A string of values as provided for the given header
     *                concatenated together using a comma. If the header does
     *                not appear in the message, this method MUST return an
     *                empty string
     */
    public function getHeaderLine($name)
    {
        $header = $this->getHeader($name);
        if (empty($header)) {
            return '';
        }

        return implode(',', $header);
    }

    /**
     * Return an instance with the specified message body.
     *
     * @param \Psr\Http\Message\StreamInterface $body Body
     *
     * @return self
     */
    public function withBody(StreamInterface $body)
    {
        $new = clone $this;
        $new->setBodyInstance($body);

        return $new;
    }

    /**
     * Gets the body of the message.
     *
     * @return \Psr\Http\Message\StreamInterface Returns the body as a stream
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets the version of HTTP protocol.
     *
     * @param string $version The version of HTTP protocol
     *
     * @throws \InvalidArgumentException If the received protocol version is not
     *                                   string or empty string
     * @throws \DomainException          If the protocol version less than 1.0
     *                                   or 2.0 and higher
     *
     * @return self
     */
    protected function setProtocol($version)
    {
        if (! is_string($version)) {
            throw new InvalidArgumentException(sprintf(
                'The HTTP protocol version must be a string, received "%s".',
                is_object($version) ? get_class($version) : gettype($version)
            ));
        }
        $version = trim($version);
        if (! $version) {
            throw new InvalidArgumentException(
                'The version of HTTP protocol can not be empty.'
            );
        }
        if (version_compare($version, '1.0', '<')) {
            throw new DomainException(sprintf(
                'The protocol "%s" is already not supported.',
                $version
            ));
        }
        if (version_compare($version, '2.0', '>=')) {
            throw new DomainException(sprintf(
                'The protocol "%s" is not yet supported.',
                $version
            ));
        }
        $this->protocol = $version;

        return $this;
    }

    /**
     * Adds the headers.
     *
     * @param array $headers The headers
     *
     * @return self
     */
    protected function addHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }

        return $this;
    }

    /**
     * Sets the header.
     *
     * @param string       $name  The header name
     * @param string|array $value The header value or array of values
     *
     * @return self
     */
    protected function setHeader($name, $value)
    {
        $name  = $this->prepareHeaderName($name);
        $value = $this->prepareHeaderValue($value);

        $normalized = strtolower($name);
        if (! isset($this->headerNames[$normalized])) {
            $this->headerNames[$normalized] = $name;
        }

        $header = $this->headerNames[$normalized];

        $this->rawHeaders[$header] = $value;

        return $this;
    }

    /**
     * Add the header.
     *
     * @param string       $name  The header name
     * @param string|array $value The header value or array of values
     *
     * @return self
     */
    protected function addHeader($name, $value)
    {
        $name  = $this->prepareHeaderName($name);
        $value = $this->prepareHeaderValue($value);

        $normalized = strtolower($name);
        if (! isset($this->headerNames[$normalized])) {
            $this->headerNames[$normalized] = $name;
        }

        $header = $this->headerNames[$normalized];

        if (isset($this->rawHeaders[$header])) {
            $this->rawHeaders[$header] = array_merge(
                $this->rawHeaders[$header],
                $value
            );

            return $this;
        }
        $this->rawHeaders[$header] = $value;

        return $this;
    }

    /**
     * Removes the header.
     *
     * @param string $name The header name
     *
     * @return self
     */
    public function removeHeader($name)
    {
        $normalized = strtolower(trim($name));
        if (! isset($this->headerNames[$normalized])) {
            return $this;
        }
        $header = $this->headerNames[$normalized];
        unset($this->rawHeaders[$header]);
        unset($this->headerNames[$normalized]);

        return $this;
    }

    /**
     * Sets the message body.
     *
     * @param string|resource|\Psr\Http\Message\StreamInterface $body The message body
     *
     * @throws \InvalidArgumentException If invalid body provided
     *
     * @return self
     */
    protected function setBody($body = 'php://temp')
    {
        if (is_string($body) || is_resource($body)) {
            $body = new Stream($body, 'w+b');
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

    /**
     * Sets the instance of stream as body of message.
     *
     * @param \Psr\Http\Message\StreamInterface $body The stream
     *
     * @return self
     */
    protected function setBodyInstance(StreamInterface $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Prepares the header name.
     *
     * @param type $name The name of header
     *
     * @throws \InvalidArgumentException If name is not string, or empty string,
     *                                   or contains illegal characters
     *
     * @return string The normalized header name
     */
    protected function prepareHeaderName($name)
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid header name "%s" provided; must be an string.',
                $name
            ));
        }

        $name = trim($name);

        if (! $name) {
            throw new InvalidArgumentException('Header name is empty.');
        }
        if (! preg_match('/^[a-zA-Z0-9\'\!#$%&\*\+-\.^_`\|~]+$/', $name)) {
            throw new InvalidArgumentException(sprintf(
                'Header name "%s" contains illegal characters.',
                $name
            ));
        }

        return $name;
    }

    /**
     * Prepares the header value.
     *
     * @param string|array $value The header value or array of values
     *
     * @throws \InvalidArgumentException If the value is not string, or empty
     *                                   string, or contains illegal characters
     *
     * @return array The array of normalized header values
     */
    protected function prepareHeaderValue($value)
    {
        if (is_string($value)) {
            $value = [$value];
        }
        if (! is_array($value)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid header value "%s"; must be a string or array of strings.',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }
        foreach ($value as &$item) {
            if (! is_string($item)) {
                throw new InvalidArgumentException(sprintf(
                    'Invalid header value "%s"; must be a string.',
                    is_object($item) ? get_class($item) : gettype($item)
                ));
            }

            $item = trim($item);

            if ('' === $item) {
                throw new InvalidArgumentException(
                    'Empty header value provided.'
                );
            }

            if (preg_match('/[^\x09\x0a\x0d\x20-\x7E\x80-\xFE]/', $item)) {
                throw new InvalidArgumentException(sprintf(
                    'The header value "%s" contains illegal characters.',
                    $item
                ));
            }
            $item = preg_replace('/\s+/', ' ', $item);
        }

        return $value;
    }
}
