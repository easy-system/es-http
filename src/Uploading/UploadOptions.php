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

use ArrayIterator;
use InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * The upload options.
 */
class UploadOptions implements UploadOptionsInterface
{
    /**
     * The array of options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Constructor.
     *
     * @param array|\Traversable|\stdClass $options The options
     *
     * @throws \InvalidArgumentException If invalid type of options provided
     */
    public function __construct($options)
    {
        if (is_array($options)) {
            $this->options = $options;

            return;
        }
        if ($options instanceof stdClass) {
            $this->options = (array) $options;

            return;
        }
        if ($options instanceof Traversable) {
            $this->options = iterator_to_array($options);

            return;
        }
        throw new InvalidArgumentException(sprintf(
            'Invalid options provided; must be an null, or array, or '
            . 'Traversable, or instance of stdClass, "%s" received.',
            is_object($options) ? get_class($options) : gettype($options)
        ));
    }

    /**
     * Gets the options as array copy.
     *
     * @return array The options
     */
    public function getArrayCopy()
    {
        return $this->options;
    }

    /**
     * Gets iterator.
     *
     * @return \ArrayIterator The iterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->options);
    }

    /**
     * Gets the option.
     *
     * @param int|string $name    The name of option
     * @param mixed      $default The default value
     *
     * @return mixed The value of option, if any, or $default otherwise
     */
    public function get($name, $default = null)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }

        return $default;
    }

    /**
     * Sets the option.
     *
     * @param int|string $name  The name of option
     * @param mixed      $value The option value
     */
    public function set($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Adds the options.
     *
     * @param array $options The options
     */
    public function add(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }
}
