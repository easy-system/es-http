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
 * The factory of Uri port.
 */
class UriPortFactory
{
    /**
     * The Uri port for forced usage.
     *
     * @var null|int
     */
    public static $forcedPort;

    /**
     * Sets the port for forced usage.
     *
     * @param null|numeric $port Optional; null by default removes a forced
     *                           port. The Uri port
     *
     * @throws \InvalidArgumentException
     *
     * - If the specified port is not a null or not a numeric
     * - If the specified port is numeric, but not between 1  and 65535
     */
    public static function setForcedPort($port = null)
    {
        if (is_null($port)) {
            static::$forcedPort = null;

            return;
        }
        if (! is_numeric($port) || $port < 1 || $port > 65535) {
            throw new InvalidArgumentException(sprintf(
                'Invalid port provided; must be a null or a numeric between '
                . '1 and 65534; "%s" received.',
                is_numeric($port) ? $port : gettype($port)
            ));
        }
        static::$forcedPort = (int) $port;
    }

    /**
     * Gets a forced Uri port, if any.
     *
     * @return null|int The Uri port
     */
    public static function getForcedPort()
    {
        return static::$forcedPort;
    }

    /**
     * Makes a Uri port. If the port for forced usage was set, returns it.
     *
     * @param array $server Optional; null by default or empty array means
     *                      global $_SERVER. The source data
     *
     * @return int The Uri port
     */
    public static function make(array $server = null)
    {
        if (static::$forcedPort) {
            return static::$forcedPort;
        }

        return static::retrieve($server);
    }

    /**
     * Retrieves the Uri port using incoming data, ignores a forced port.
     * If unable to retrieve port, returns 80 by default.
     *
     * @param array $server Optional; null by default or empty array means
     *                      global $_SERVER. The source data
     *
     * @return int The Uri port
     */
    public static function retrieve(array $server = null)
    {
        if (empty($server)) {
            $server = $_SERVER;
        }

        $port = '80';

        if (isset($server['SERVER_PORT'])) {
            $port = $server['SERVER_PORT'];
        }

        return (int) $port;
    }
}
