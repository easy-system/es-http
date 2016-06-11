<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http\Uploading;

/**
 * The interface of upload options.
 */
interface UploadOptionsInterface extends \IteratorAggregate
{
    /**
     * Gets the options as array copy.
     *
     * @return array The options
     */
    public function getArrayCopy();

    /**
     * Gets iterator.
     *
     * @return \ArrayIterator The iterator
     */
    public function getIterator();

    /**
     * Gets the option.
     *
     * @param int|string $name    The name of option
     * @param mixed      $default The default value
     *
     * @return mixed The value of option, if any, or $default otherwise
     */
    public function get($name, $default = null);

    /**
     * Sets the option.
     *
     * @param int|string $name  The name of option
     * @param mixed      $value The option value
     */
    public function set($name, $value);

    /**
     * Adds the options.
     *
     * @param array $options The options
     */
    public function add(array $options);
}
