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
use Psr\Http\Message\UriInterface;

/**
 * Provides a value object representing a URI.
 */
class Uri implements UriInterface
{
    /**
     * Unreserved characters used in URIs.
     *
     * @const string
     */
    const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';

    /**
     * General delimiters used in URIs.
     *
     * | : | / | ? | # | ] | [ | @ |
     *
     * @const string
     */
    const CHAR_GEN_DELIMS = ':\/\?#\]\[@';

    /**
     * Sub-delimiters used in URIs.
     *
     * | ! | $ | & | ' | ) | ( | * | + | , | ; | = |
     *
     * @const string
     */
    const CHAR_SUB_DELIMS = '!\$&\'\)\(\*\+,;=';

    /**
     * The URI scheme.
     *
     * @var string
     */
    protected $scheme = '';

    /**
     * The URI user info.
     *
     * @var string
     */
    protected $userInfo = '';

    /**
     * The URI host.
     *
     * @var string
     */
    protected $host = '';

    /**
     * The URI path.
     *
     * @var string
     */
    protected $path = '';

    /**
     * The URI query.
     *
     * @var string
     */
    protected $query = '';

    /**
     * The URI fragment.
     *
     * @var string
     */
    protected $fragment = '';

    /**
     * The URI port.
     *
     * @var null|int
     */
    protected $port;

    /**
     * Generated URI string cache.
     *
     * @var null|string
     */
    protected $uriString;

    /**
     * URI-encode according to RFC 3986.
     *
     * @param string $value  The string to be encoded
     * @param string $ignore Optional; service symbols by default. Characters
     *                       that will not encoded
     *
     * @return string The encoded string
     */
    public static function encode($value, $ignore = null)
    {
        if (null === $ignore) {
            $ignore = static::CHAR_UNRESERVED
                    . static::CHAR_GEN_DELIMS
                    . static::CHAR_SUB_DELIMS;
        }
        $rawurlencode = function (array $matches) {
            return rawurlencode($matches[0]);
        };

        return preg_replace_callback(
            '/(?:[^' . $ignore . '%]+|%(?![A-Fa-f0-9]{2}))/',
            $rawurlencode,
            $value
        );
    }

    /**
     * Decode URI-encoded strings.
     *
     * @param string $value The encoded string
     *
     * @return string The decoded string
     */
    public static function decode($value)
    {
        return rawurldecode($value);
    }

    /**
     * Constructor.
     *
     * @param string $uri Optional; the uri string
     */
    public function __construct($uri = null)
    {
        if (null !== $uri) {
            $this->fromString($uri);
        }
    }

    /**
     * Return an instance with the provided scheme.
     *
     * @param string $scheme The scheme to use with the new instance
     *
     * @return Uri The instance with the provided scheme
     */
    public function withScheme($scheme)
    {
        $new = clone $this;
        $new->setScheme($scheme);

        return $new;
    }

    /**
     * Retrieve the scheme component of the URI.
     *
     * @return string The URI scheme
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Return an instance with the provided user information.
     * Password is optional, but the user information MUST include the
     * user; an empty string for the user is equivalent to removing user
     * information.
     *
     * The userinfo subcomponent for http URI Scheme is deprecated.
     * @see http://tools.ietf.org/html/rfc7230#section-2.7.1
     *
     * @param string $user     The user name to use for authority
     * @param string $password The password associated with $user
     *
     * @return Uri The instance with the provided user information
     */
    public function withUserInfo($user, $password = null)
    {
        $new = clone $this;
        $new->setUserInfo($user, $password);

        return $new;
    }

    /**
     * Retrieve the user information component of the URI.
     *
     * The userinfo subcomponent for http URI Scheme is deprecated.
     * @see http://tools.ietf.org/html/rfc7230#section-2.7.1
     *
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * Return an instance with the provided host.
     *
     * @param string $host The hostname to use with the new instance
     *
     * @return Uri The instance with the provided host
     */
    public function withHost($host)
    {
        $new = clone $this;
        $new->setHost($host);

        return $new;
    }

    /**
     * Retrieve the host component of the URI.
     *
     * @return string The URI host
     */
    public function getHost()
    {
        if ($this->scheme === 'file' && ! $this->host) {
            return 'localhost';
        }

        return $this->host;
    }

    /**
     * Return an instance with the provided port.
     * A null value provided for the port is equivalent to removing the port
     * information.
     *
     * @param null|int $port The port to use with the new instance; a null value
     *                       removes the port information
     *
     * @throw \InvalidArgumentException If invalid port provided
     *
     * @return Uri The instance with the provided port
     */
    public function withPort($port)
    {
        $new = clone $this;
        try {
            $new->setPort($port);
        } catch (InvalidArgumentException $ex) {
            throw new InvalidArgumentException('Invalid port provided.', null, $ex);
        }

        return $new;
    }

    /**
     * Retrieve the port component of the URI.
     * If a port is present, and it is non-standard for the current scheme,
     * this method returns it as an integer. If the port is the standard port
     * used with the current scheme, this method returns null.
     *
     * @return int|null The URI port
     */
    public function getPort()
    {
        $port = $this->port;
        if ($port && $port != $this->getStandartPort()) {
            return $port;
        }
    }

    /**
     * Returns the standard port used with the current scheme.
     *
     * @return int|false The standard port used with the current scheme or false
     *                   if unable to determine
     */
    public function getStandartPort()
    {
        return getservbyname($this->scheme, 'tcp');
    }

    /**
     * Checks if the port is valid.
     *
     * @param mixed $port The port
     *
     * @return bool The result of validation
     */
    public function isPortValid($port)
    {
        if (! is_scalar($port) || is_array($port)) {
            return false;
        }

        return 1 < $port && $port < 65535;
    }

    /**
     * Retrieve the authority component of the URI.
     *
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {
        $host = $this->getHost();
        if (! $host) {
            return '';
        }
        $authority = $host;

        $userInfo = $this->getUserInfo();
        if ($userInfo) {
            $authority = $userInfo . '@' . $host;
        }

        $port = $this->getPort();
        if ($port) {
            $authority .= ':' . $port;
        }

        return $authority;
    }

    /**
     * Return an instance with the provided path.
     * The path can either be empty or host-relative (starting with a slash) or
     * location-relative (not starting with a slash).
     *
     * @param string $path The path to use with the new instance
     *
     * @throw \InvalidArgumentException If invalid path provided
     *
     * @return Uri The instance with the provided path
     */
    public function withPath($path)
    {
        $new = clone $this;
        try {
            $new->setPath($path);
        } catch (InvalidArgumentException $ex) {
            throw new InvalidArgumentException('Invalid path provided.', null, $ex);
        }

        return $new;
    }

    /**
     * Retrieve the path component of the URI.
     * The returned value is percent-encoded, except for service symbols.
     * If your case requires more in-depth encoding, use Uri::encode() method.
     *
     * @return string The URI path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Return an instance with the provided query string.
     *
     * @param string $query The query string to use with the new instance
     *
     * @throws \InvalidArgumentException If invalid query provided
     *
     * @return Uri The instance with the provided query string
     */
    public function withQuery($query)
    {
        $new = clone $this;
        try {
            $new->setQuery($query);
        } catch (InvalidArgumentException $ex) {
            throw new InvalidArgumentException('Invalid query provided.', null, $ex);
        }

        return $new;
    }

    /**
     * Retrieve the query string of the URI.
     * The returned value is percent-encoded, except for service symbols.
     * If your case requires more in-depth encoding, use Uri::encode() method.
     *
     * @return string The query string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Return an instance with the provided URI fragment.
     *
     * @param string $fragment The fragment to use with the new instance
     *
     * @return Uri The instance with the provided URI fragment
     */
    public function withFragment($fragment)
    {
        $new = clone $this;
        $new->setFragment($fragment);

        return $new;
    }

    /**
     * Retrieve the fragment component of the URI.
     * The returned value is percent-encoded, except for service symbols.
     * If your case requires more in-depth encoding, use Uri::encode() method.
     *
     * @return string The fragment component of the URI
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Return the string representation as a URI reference.
     *
     * @return string The string representation
     */
    public function __toString()
    {
        if (null === $this->uriString) {
            $this->uriString = static::createUriString(
                $this->getScheme(),
                $this->getAuthority(),
                $this->getPath(),
                $this->getQuery(),
                $this->getFragment()
            );
        }

        return $this->uriString;
    }

    /**
     * Create a URI string from its various parts.
     *
     * @param string $scheme    The scheme
     * @param string $authority The authority
     * @param string $path      The path
     * @param string $query     The query
     * @param string $fragment  The fragment
     *
     * @return string The string representation of URI
     */
    protected static function createUriString($scheme, $authority, $path, $query, $fragment)
    {
        $uri = '';
        if ($scheme && ($authority || $scheme !== 'http')) {
            $uri .= $scheme . ':';
        }
        if ($authority) {
            $uri .= '//';
            if ('file' === $scheme && 0 === strpos($authority, 'localhost')) {
                $uri .= substr($authority, 9);
            } else {
                $uri .= $authority;
            }
        }
        if ($path) {
            if ($authority) {
                $path = '/' . ltrim($path, '/');
            }
            $uri .= $path;
        }
        if ($query) {
            $uri .= sprintf('?%s', $query);
        }
        if ($fragment) {
            $uri .= sprintf('#%s', $fragment);
        }

        return $uri;
    }

    /**
     * Forms the an object from an URI string.
     *
     * @param string $uri The URI string
     *
     * @throws InvalidArgumentException On non-string $uri argument
     *
     * @return Uri The current instance
     */
    protected function fromString($uri)
    {
        if (! is_string($uri)) {
            throw new InvalidArgumentException(sprintf(
                '"%s()" expects string; "%s" received.',
                __METHOD__,
                is_object($uri) ? get_class($uri) : gettype($uri)
            ));
        }

        $this
            ->setUserInfo(
                parse_url($uri, PHP_URL_USER),
                parse_url($uri, PHP_URL_PASS)
            )
            ->setScheme(parse_url($uri,   PHP_URL_SCHEME))
            ->setHost(parse_url($uri,     PHP_URL_HOST))
            ->setPath(parse_url($uri,     PHP_URL_PATH))
            ->setQuery(parse_url($uri,    PHP_URL_QUERY))
            ->setFragment(parse_url($uri, PHP_URL_FRAGMENT));

        if ($port = parse_url($uri, PHP_URL_PORT)) {
            $this->setPort($port);
        }

        return $this;
    }

    /**
     * Sets the URI scheme to current instance.
     *
     * @param string $scheme The URI scheme
     *
     * @return Uri The current instance with the provided scheme
     */
    protected function setScheme($scheme)
    {
        $this->uriString = null;

        $this->scheme = strtolower(rtrim($scheme, ':/'));

        return $this;
    }

    /**
     * Sets the provided user information to current instance.
     *
     * @param string $user     The user name to use for authority
     * @param string $password The password associated with $user
     *
     * @return Uri The current instance with the provided user information
     */
    protected function setUserInfo($user = '', $password = '')
    {
        $this->uriString = null;

        if (! $user) {
            $this->userInfo = '';

            return $this;
        }
        $this->userInfo = rtrim($user, '@');

        if ($password) {
            $this->userInfo .= ':' . $password;
        }

        return $this;
    }

    /**
     * Sets the provided host to current instance.
     *
     * @param string $host The hostname
     *
     * @return Uri The current instance with the provided host
     */
    protected function setHost($host)
    {
        $this->uriString = null;

        $this->host = (string) $host;

        return $this;
    }

    /**
     * Sets the provided port to current instance.
     *
     * @param null|int $port The port; a null value removes the port information
     *
     * @throw \InvalidArgumentException For invalid ports
     *
     * @return Uri The current instance with the provided port
     */
    protected function setPort($port)
    {
        $this->uriString = null;

        if (null === $port) {
            $this->port = null;

            return $this;
        }

        if (! $this->isPortValid($port)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid port "%s" provided.',
                (is_object($port) ? get_class($port) : gettype($port))
            ));
        }
        $this->port = (int) $port;

        return $this;
    }

    /**
     * Sets the provided path to current instance.
     *
     * @param string $path The path
     *
     * @throws \InvalidArgumentException If path contain query string or
     *                                   URI fragment
     *
     * @return Uri The current instance with the provided path
     */
    protected function setPath($path)
    {
        $this->uriString = null;

        $path = (string) $path;

        if (strpos($path, '?') !== false) {
            throw new InvalidArgumentException(
                'Invalid path provided; must not contain a query string.'
            );
        }

        if (strpos($path, '#') !== false) {
            throw new InvalidArgumentException(
                'Invalid path provided; must not contain a URI fragment.'
            );
        }

        $this->path = static::encode($path);

        return $this;
    }

    /**
     * Sets the provided query string to current instance.
     *
     * @param string $query The query string
     *
     * @throws \InvalidArgumentException If query string contain URI fragment
     *
     * @return Uri The current instance with the provided query string
     */
    protected function setQuery($query)
    {
        $this->uriString = null;

        $query = ltrim($query, '?');

        if (strpos($query, '#') !== false) {
            throw new InvalidArgumentException(
                'Query string must not include a URI fragment.'
            );
        }

        $this->query = static::encode($query);

        return $this;
    }

    /**
     * Sets the provided URI fragment to current instance.
     *
     * @param type $fragment The URI fragment
     *
     * @return Uri The current instance with the provided URI fragment
     */
    protected function setFragment($fragment)
    {
        $this->uriString = null;

        $fragment = ltrim($fragment, '#');

        $this->fragment = static::encode($fragment);

        return $this;
    }
}
