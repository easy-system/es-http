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
use InvalidArgumentException;
use RuntimeException;

/**
 * The queue that allow to create a specified sequence of strategies.
 */
class StrategiesQueue implements UploadStrategyInterface
{
    /**
     * An array of priorities and its appropriate strategies.
     *
     * @var array
     */
    protected $queue = [];

    /**
     * The upload options.
     *
     * @var UploadOptionsInterface
     */
    private $options;

    /**
     * The sorted copy of internal queue.
     *
     * @var null|array
     */
    private $sortedQueue;

    /**
     * Is __invoke() started?
     *
     * @var bool
     */
    protected $started = false;

    /**
     * Attaches the upload strategy to specific priority.
     *
     * @param UploadStrategyInterface $strategy The upload strategy
     * @param int                     $priority The priority
     *
     * @throws \RuntimeException         If try to attach during invoking
     * @throws \InvalidArgumentException If the specified priority have already
     *                                   some strategy
     *
     * @return self
     */
    public function attach(UploadStrategyInterface $strategy, $priority)
    {
        if ($this->started) {
            throw new RuntimeException('Unable during invoking.');
        }
        if (isset($this->queue[$priority])) {
            throw new InvalidArgumentException(sprintf(
                'The strategy with priority "%s" already exists.',
                $priority
            ));
        }
        $this->queue[$priority] = $strategy;
        $this->sortedQueue      = null;

        if ($this->options && ! $strategy->getOptions()) {
            $strategy->setOptions($this->options);
        }

        return $this;
    }

    /**
     * Detaches of the upload strategy with the specific priority.
     *
     * @param int $priority The priority
     *
     * @throws \RuntimeException If try to detach during invoking
     *
     * @return self
     */
    public function detach($priority)
    {
        if ($this->started) {
            throw new RuntimeException('Unable during invoking.');
        }
        if (isset($this->queue[$priority])) {
            unset($this->queue[$priority]);
            $this->sortedQueue = null;
        }

        return $this;
    }

    /**
     * Gets the queue as array copy.
     *
     * @return array The queue
     */
    public function getArrayCopy()
    {
        return $this->queue;
    }

    /**
     * Sets the upload options.
     *
     * @param UploadOptionsInterface $options The upload options
     */
    public function setOptions(UploadOptionsInterface $options)
    {
        foreach ($this->queue as $item) {
            if (! $item->getOptions()) {
                $item->setOptions($options);
            }
        }
        $this->options = $options;
    }

    /**
     * Gets the upload options.
     *
     * @return null|UploadOptionsInterface The upload options, if any
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Gets the state of strategy.
     *
     * @return null|int The state of strategy
     */
    public function getState()
    {
        if (empty($this->sortedQueue)) {
            return;
        }
        $current = current($this->sortedQueue)
                 ? current($this->sortedQueue)
                 : end($this->sortedQueue);

        return $current->getState();
    }

    /**
     * An easy way to check the state to the presence of failure.
     *
     * Any of strategies can decides on failure without break the execution.
     * The queue of strategies returns result of last executed strategy, if any.
     *
     * @return bool Returns true, if the state contains the failure flag,
     *              false otherwise
     */
    public function hasOperationError()
    {
        if (empty($this->sortedQueue)) {
            return false;
        }
        $current = current($this->sortedQueue)
                 ? current($this->sortedQueue)
                 : end($this->sortedQueue);

        return (bool) ($current::STATE_FAILURE & $current->getState());
    }

    /**
     * Gets the error of last operation.
     *
     * @return null|string The error of last operation, if any
     */
    public function getOperationError()
    {
        if (empty($this->sortedQueue)) {
            return;
        }
        $current = current($this->sortedQueue)
                 ? current($this->sortedQueue)
                 : end($this->sortedQueue);

        return $current->getOperationError();
    }

    /**
     * Gets the description of error of last operation.
     *
     * @return null|string The description of error of last operation, if any
     */
    public function getOperationErrorDescription()
    {
        if (empty($this->sortedQueue)) {
            return;
        }
        $current = current($this->sortedQueue)
                 ? current($this->sortedQueue)
                 : end($this->sortedQueue);

        return $current->getOperationErrorDescription();
    }

    /**
     * Runs all strategies in queue.
     *
     * @param \Es\Http\UploadedFileInterface $file   The value object, that
     *                                               represents uploaded file
     * @param UploadTargetInterface          $target The target of upload
     */
    public function __invoke(UploadedFileInterface $file, UploadTargetInterface $target)
    {
        if (! $this->sortedQueue) {
            $this->sortedQueue = $this->queue;
            krsort($this->sortedQueue);
        }
        for (
            $this->started = true, reset($this->sortedQueue);
            $item = current($this->sortedQueue);
            next($this->sortedQueue)
        ) {
            $item($file, $target);
            if ($item->getState() & $item::STATE_BREAK) {
                break;
            }
        }
        $this->started = false;
    }
}
