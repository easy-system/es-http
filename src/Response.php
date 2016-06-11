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
use Psr\Http\Message\ResponseInterface;

/**
 * Representation of an outgoing, server-side response.
 */
class Response extends Message implements ResponseInterface
{
    /**
     * The phrases corresponding to codes of states.
     *
     * @var array
     */
    protected $phrases = array(
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated
        307 => 'Temporary Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    );

    /**
     * The custom phrase corresponding to current code of state.
     *
     * @var string
     */
    protected $reasonPhrase = '';

    /**
     * The current code of state.
     *
     * @var int
     */
    protected $statusCode = 200;

    /**
     * Constructor.
     *
     * @param null|int|string $status Optional; the status code,
     *
     * must be a 3-digit integer result code between 100 and 599
     * <!-- -->
     * @param null|string|resource|\Psr\Http\Message\StreamInterface $body Optional;
     *
     * the body of response
     * <!-- -->
     * @param null|array $headers Optional; the HTTP headers
     *
     * <!-- -->
     * @param null|string $protocol Optional; the version of HTTP protocol
     */
    public function __construct(
        $status = null,
        $body = null,
        array $headers = null,
        $protocol = null
    ) {
        parent::__construct($body, $headers, $protocol);

        if (null !== $status) {
            $this->setStatus($status);
        }
    }

    /**
     * Gets the response status code.
     *
     * @return int The status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Return an instance with the specified status code and, optionally,
     * reason phrase.
     *
     * @param int|string $code         The 3-digit result code between
     *                                 100 and 599
     * @param string     $reasonPhrase Optional; the reason phrase to use with
     *                                 the provided status code
     *
     * @return self
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $new = clone $this;
        $new->setStatus($code, $reasonPhrase);

        return $new;
    }

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * @return string The reason phrase
     */
    public function getReasonPhrase()
    {
        if ($this->reasonPhrase) {
            return $this->reasonPhrase;
        }

        $status = $this->getStatusCode();
        if (isset($this->phrases[$status])) {
            return $this->phrases[$status];
        }

        return '';
    }

    /**
     * Sets the status code and, optionally, the reason phrase.
     *
     * @param int|string $code         The 3-digit result code between
     *                                 100 and 599
     * @param string     $reasonPhrase Optional; the reason phrase to use with
     *                                 the provided status code
     *
     * @throws \InvalidArgumentException If the status code is invalid or
     *                                   reason phrase is not string
     *
     * @return self
     */
    protected function setStatus($code, $reasonPhrase = '')
    {
        if (! is_string($reasonPhrase)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid reason phrase provided; must be a string, "%s" '
                . 'received.',
                is_object($reasonPhrase) ? get_class($reasonPhrase)
                                         : gettype($reasonPhrase)
            ));
        }
        if (! preg_match('#\A\d{3}\Z#', $code)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid status code provided; must be a 3-digit integer result'
                . ' code, "%s" received.',
                is_scalar($code) ? $code : gettype($code)
            ));
        }
        if ($code < 100 || $code > 599) {
            throw new InvalidArgumentException(
                'Invalid status code provided; must be between 100 and 599.'
            );
        }
        $this->statusCode   = (int) $code;
        $this->reasonPhrase = $reasonPhrase;

        return $this;
    }
}
