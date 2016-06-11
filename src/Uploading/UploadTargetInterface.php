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
 * Interface of target of upload.
 */
interface UploadTargetInterface
{
    /**
     * Sets the target of upload.
     *
     * @param string $target The target of upload
     *
     * @throws \InvalidArgumentException If target of upload has invalid format
     */
    public function set($target);

    /**
     * Gets the target of upload.
     *
     * @return mixed The target of upload
     */
    public function get();

    /**
     * Gets the string representation.
     *
     * @return string The string representation of target of upload
     */
    public function __toString();
}
