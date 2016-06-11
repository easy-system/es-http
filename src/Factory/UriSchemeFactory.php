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
 * The factory of Uri scheme.
 */
class UriSchemeFactory
{
    /**
     * The Uri scheme for forced usage.
     *
     * @var null|strig
     */
    protected static $forcedScheme;

    /**
     * Sets the Uri scheme for forced usage.
     *
     * @param null|string $scheme Optional; null by default removes a forced
     *                            scheme. The Uri scheme
     *
     * @throws \InvalidArgumentException If the received scheme is not a string
     *                                   or not a null
     */
    public static function setForcedScheme($scheme = null)
    {
        if (! is_string($scheme) && ! is_null($scheme)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid scheme provided; must be an string or a null, "%s" received.',
                is_object($scheme) ? get_class($scheme) : gettype($scheme)
            ));
        }
        static::$forcedScheme = $scheme;
    }

    /**
     * Gets a forced Uri scheme, if any.
     *
     * @return null|string Returns the forced Uri scheme, if any; null otherwise
     */
    public static function getForcedScheme()
    {
        return static::$forcedScheme;
    }

    /**
     * Makes a string with Uri scheme. If the forced scheme was set, returns it.
     *
     * @param array $server Optional; null by default or empty array means
     *                      global $_SERVER. The source data
     *
     * @return string Returns the Uri scheme, if any, or 'http' otherwise
     */
    public static function make(array $server = null)
    {
        if (static::$forcedScheme) {
            return static::$forcedScheme;
        }

        return static::retrieve($server);
    }

    /**
     * Retrieves the Uri scheme using incoming data, ignores a forced scheme.
     * If unable to retrieve the scheme, returns 'http'.
     *
     * @param array $server Optional; null by default or empty array means
     *                      global $_SERVER. The source data
     *
     * @return string Returns the Uri scheme if any, 'http' otherwise
     */
    public static function retrieve(array $server = null)
    {
        if (empty($server)) {
            $server = $_SERVER;
        }

        $scheme = 'http';

        if (isset($server['HTTPS']) && 'off' != $server['HTTPS']) {
            $scheme = 'https';
        }

        return $scheme;
    }
}
