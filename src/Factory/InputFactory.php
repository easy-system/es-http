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

use Es\Http\Stream;
use InvalidArgumentException;

/**
 * The factory of stream, that contains the request body.
 */
class InputFactory
{
    /**
     * The data source.
     *
     * @var null|resource
     */
    protected static $source;

    /**
     * Sets data source.
     *
     * @param null|resource $source
     *
     * @throws \InvalidArgumentException If invalid source provided
     */
    public static function setSource($source = null)
    {
        if (! is_resource($source) && ! is_null($source)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid source provided; must be a null or an resource, "%s" given.',
                is_object($source) ? get_class($source) : gettype($source)
            ));
        }
        static::$source = $source;
    }

    /**
     * Gets data source.
     *
     * @return resource
     */
    public static function getSource()
    {
        if (static::$source) {
            return static::$source;
        }
        $source = fopen('php://input', 'rb');

        return $source;
    }

    /**
     * Makes a stream and and fills it with input data.
     *
     * @return \Es\Http\Stream The stream
     */
    public static function make()
    {
        $stream = new Stream();
        $source = static::getSource();
        $stream->copy($source);

        if (! static::$source) {
            fclose($source);
        }

        return $stream;
    }
}
