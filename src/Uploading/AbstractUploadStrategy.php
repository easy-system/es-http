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

use UnexpectedValueException;

/**
 * The representation of basic functionality of abstract strategy.
 */
abstract class AbstractUploadStrategy implements UploadStrategyInterface
{
    /**
     * The state of strategy.
     *
     * @var null|int
     */
    protected $state;

    /**
     * The error of last operation.
     *
     * @var null|string
     */
    protected $operationError;

    /**
     * The description of error of last operation.
     *
     * @var null|string
     */
    protected $operationErrorDescription;

    /**
     * The array with errors as array keys and error descriptions as array
     * values.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * The upload options.
     *
     * @var null|UploadOptionsInterface
     */
    private $options;

    /**
     * Sets the upload options.
     *
     * @param UploadOptionsInterface $options The options
     */
    public function setOptions(UploadOptionsInterface $options)
    {
        foreach ($options as $name => $value) {
            $setter = 'set' . str_replace('_', '', $name);
            if (method_exists($this, $setter)) {
                $this->{$setter}($value);
            }
        }
        $this->options = $options;
    }

    /**
     * The upload options.
     *
     * @return null|UploadOptionsInterface The options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Gets the state of strategy.
     *
     * @return null|int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * An easy way to check the state to the presence of failure.
     *
     * @return bool Returns true, if the state contains the failure flag,
     *              false otherwise
     */
    public function hasOperationError()
    {
        return (bool) ($this->state & static::STATE_FAILURE);
    }

    /**
     * Gets the error of last operation, if any.
     *
     * @return null|string
     */
    public function getOperationError()
    {
        return $this->operationError;
    }

    /**
     * Gets the description of error of last operation, if any.
     *
     * @return null|string
     */
    public function getOperationErrorDescription()
    {
        return $this->operationErrorDescription;
    }

    /**
     * Decides on failure.
     *
     * @param string $error The error
     *
     * @throws \UnexpectedValueException If the received error is not specified
     *                                   in $this->errors
     */
    protected function decideOnFailure($error)
    {
        if (! isset($this->errors[$error])) {
            throw new UnexpectedValueException(sprintf(
                'Unexpected error "%s" received. All error must be specified '
                . 'in errors array of this instance.',
                $error
            ));
        }
        $this->state = static::STATE_FAILURE | static::STATE_BREAK;

        $this->operationError            = $error;
        $this->operationErrorDescription = $this->errors[$error];
    }

    /**
     * Decides on success.
     */
    protected function decideOnSuccess()
    {
        $this->state = static::STATE_SUCCESS;

        $this->operationError            = null;
        $this->operationErrorDescription = null;
    }
}
