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

use Es\Http\UploadedFileInterface;

/**
 * Representation of uploading strategy.
 */
interface UploadStrategyInterface
{
    /**
     * The state of strategy, if the last operation has success.
     *
     * @const int
     */
    const STATE_SUCCESS = 0b00;

    /**
     * The state of strategy, if the last operation has failed.
     *
     * @const int
     */
    const STATE_FAILURE = 0b01;

    /**
     * The state of strategy, if all further operations must be stopped.
     */
    const STATE_BREAK = 0b10;

    /**
     * Sets the upload options.
     *
     * @param UploadOptionsInterface $options The upload options
     */
    public function setOptions(UploadOptionsInterface $options);

    /**
     * Gets the upload options.
     *
     * @return null|UploadOptionsInterface The upload options, if any
     */
    public function getOptions();

    /**
     * Performs operation.
     *
     * @param \Es\Http\UploadedFileInterface $file   The value object, that
     *                                               represents uploaded file
     * @param UploadTargetInterface          $target The target of upload
     */
    public function __invoke(UploadedFileInterface $file, UploadTargetInterface $target);

    /**
     * An easy way to check the state to the presence of failure.
     *
     * @return bool Returns true, if the state contains the failure flag,
     *              false otherwise
     */
    public function hasOperationError();

    /**
     * Gets the error of last operation, if any.
     *
     * @return null|string
     */
    public function getOperationError();

    /**
     * Gets the description of error of last operation, if any.
     *
     * @return null|string
     */
    public function getOperationErrorDescription();

    /**
     * Gets the state of strategy.
     *
     * @return null|int
     */
    public function getState();
}
