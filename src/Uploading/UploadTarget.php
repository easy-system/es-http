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

use InvalidArgumentException;

/**
 * The target of upload.
 */
class UploadTarget implements UploadTargetInterface
{
    /**
     * The target of upload.
     *
     * @var string
     */
    protected $target = '';

    /**
     * Constructor.
     *
     * @param string $target
     */
    public function __construct($target)
    {
        $this->set($target);
    }

    /**
     * Sets the target of upload.
     *
     * @param string $target The target of upload
     *
     * @throws \InvalidArgumentException If the target is not string or empty string
     */
    public function set($target)
    {
        if (! is_string($target) || empty($target)) {
            throw new InvalidArgumentException(
                'Invalid target provided. Must be an non-empty string.'
            );
        }
        $this->target = $target;
    }

    /**
     * Gets the target of upload.
     *
     * @return string The target of upload
     */
    public function get()
    {
        return $this->target;
    }

    /**
     * Gets the string representation.
     *
     * @return string The string representation of target of upload
     */
    public function __toString()
    {
        return $this->target;
    }
}
