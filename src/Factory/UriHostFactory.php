<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http\Factory;

use InvalidArgumentException;

/**
 * The factory of a name of the server host under which the current script
 * is executing.
 */
class UriHostFactory
{
    /**
     * The host name for forced usage.
     *
     * @var null|strig
     */
    protected static $forcedHost;

    /**
     * Sets the host name for forced usage.
     *
     * @param null|string $host Optional; null by default removes a forced host.
     *                          The host name
     *
     * @throws \InvalidArgumentException If received hos is not a string or
     *                                   not a null
     */
    public static function setForcedHost($host = null)
    {
        if (! is_string($host) && ! is_null($host)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid host provided; must be an string or a null, "%s" received.',
                is_object($host) ? get_class($host) : gettype($host)
            ));
        }
        static::$forcedHost = $host;
    }

    /**
     * Gets a forced host name, if any.
     *
     * @return null|string Returns the forced host name, if any; null otherwise
     */
    public static function getForcedHost()
    {
        return static::$forcedHost;
    }

    /**
     * Makes a string with the name of the server host under which the current
     * script is executing. If the host for forced usage was set, returns it.
     *
     * @param array $server Optional; null by default or empty array means
     *                      global $_SERVER. The source data
     *
     * @return string Returns the host name on success, null otherwise
     */
    public static function make(array $server = null)
    {
        if (! empty(static::$forcedHost)) {
            return static::$forcedHost;
        }

        return static::retrieve($server);
    }

    /**
     * Retrieves the host name using incoming data, ignores a forced host.
     * If unable to retrive the host name, try to retrieve server address.
     *
     * @param array $server Optional; null by default or empty array means
     *                      global $_SERVER. The source data
     *
     * @return string Returns the host name on success, null otherwise
     */
    public static function retrieve(array $server = null)
    {
        if (empty($server)) {
            $server = $_SERVER;
        }

        if (isset($server['SERVER_NAME'])) {
            return $server['SERVER_NAME'];
        }

        $address = '';

        if (isset($server['SERVER_ADDR'])) {
            $address = $server['SERVER_ADDR'];
        }

        if (isset($server['LOCAL_ADDR'])) {
            $address = $server['LOCAL_ADDR'];
        }

        if (false !== strpos($address, ':')) {
            $address = '[' . $address . ']';
        }

        if ($address) {
            return $address;
        }
    }
}
