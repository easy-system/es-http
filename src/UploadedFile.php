<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http;

use Es\Http\Uploading\DefaultUploadStrategy;
use Es\Http\Uploading\UploadOptions;
use Es\Http\Uploading\UploadOptionsInterface;
use Es\Http\Uploading\UploadStrategyInterface;
use Es\Http\Uploading\UploadTarget;
use Es\Http\Uploading\UploadTargetInterface;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Value object representing a file uploaded through an HTTP request.
 */
class UploadedFile implements UploadedFileInterface
{
    /**
     * The upload strategy.
     *
     * @var \Es\Http\Upload\UploadStrategyInterface
     */
    protected $strategy;

    /**
     * The stream representing the uploaded file.
     *
     * @var null|\Psr\Http\Message\StreamInterface
     */
    protected $stream;

    /**
     * The original name of the file on the client machine.
     *
     * @var null|string
     */
    protected $clientFileName;

    /**
     * The temporary filename of the file in which the uploaded file was stored
     * on the server.
     *
     * @var null|string
     */
    protected $tempName;

    /**
     * The mime type of the file, if the browser provided this information.
     *
     * @var null|string
     */
    protected $clientMediaType;

    /**
     * The size, in bytes, of the uploaded file.
     *
     * @var null|int
     */
    protected $size;

    /**
     * The error code associated with this file upload.
     *
     * @var int
     */
    protected $error = 0;

    /**
     * Was there already an attempt to move a file?
     *
     * @var bool
     */
    protected $moved = false;

    /**
     * Constructor.
     *
     * @param null|string $clientName The original name of the file on the
     *                                client machine
     * @param null|string $tempName   The temporary filename of the file in
     *                                which the uploaded file was stored on
     *                                the server
     * @param null|string $mediaType  The mime type of the file, if the browser
     *                                provided this information
     * @param null|int    $size       The size, in bytes, of the uploaded file
     * @param null|int    $error      The error code associated with this file
     *                                upload, must be one of PHP's
     *                                UPLOAD_ERR_XXX constants
     */
    public function __construct(
        $clientName = null,
        $tempName = null,
        $mediaType = null,
        $size = null,
        $error = null
    ) {
        if ($clientName) {
            $this->setClientFileName($clientName);
        }
        if ($tempName) {
            $this->setTempName($tempName);
        }
        if ($mediaType) {
            $this->setClientMediaType($mediaType);
        }
        if ($size) {
            $this->setSize($size);
        }
        if ($error) {
            $this->setError($error);
        }
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * @return null|string The original name of the file on the client machine
     *                     or null if none was provided
     */
    public function getClientFilename()
    {
        return $this->clientFileName;
    }

    /**
     * Retrive the temporary filename of the file in which the uploaded file was
     * stored on the server.
     *
     * @return null|string The path to temporary file or null if none
     *                     was provided
     */
    public function getTempName()
    {
        return $this->tempName;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * @return null|string The media type sent by the client or null if none
     *                     was provided
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }

    /**
     * Retrieve the file size.
     *
     * @return null|int The file size in bytes or null if unknown
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * @return int One of PHP's UPLOAD_ERR_XXX constants
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Sets a stream representing the uploaded file.
     *
     * @param \Psr\Http\Message\StreamInterface $stream Stream representation
     *                                                  of the uploaded file
     */
    public function setStream(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * @throws \RuntimeException
     *
     * - If file is already moved
     * - If the temporary name is not specified and the stream was not set
     *
     * @return \Psr\Http\Message\StreamInterface Stream representation of the
     *                                           uploaded file
     */
    public function getStream()
    {
        if ($this->moved) {
            throw new RuntimeException('The file is already moved.');
        }
        if ($this->tempName) {
            $this->stream = new Stream($this->tempName);
        }
        if (! $this->stream) {
            throw new RuntimeException('The stream was not set.');
        }

        return $this->stream;
    }

    /**
     * Sets the upload strategy.
     *
     * Since the Uploaded File is an object-value, and besides the Uploading
     * itself is specific to each application, the mechanism of uploads is
     * represented as a separate interface.
     *
     * @param \Es\Http\Uploading\UploadStrategyInterface $strategy The upload
     *                                                             strategy
     *
     * @return self
     */
    public function setUploadStrategy(UploadStrategyInterface $strategy)
    {
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * Retrieve a upload strategy.
     *
     * If the upload strategy was not set, returns the simple default strategy.
     *
     * @return \Es\Http\Uploading\UploadStrategyInterface The upload strategy
     */
    public function getUploadStrategy()
    {
        if (! $this->strategy) {
            $this->strategy = new DefaultUploadStrategy();
        }

        return $this->strategy;
    }

    /**
     * Move the uploaded file to a new location.
     *
     * @param mixed $target  The target of upload
     * @param mixed $options Optional; the upload options
     *
     * @throws \RuntimeException If the file is already moved
     *
     * @return \Es\Http\Uploading\UploadStrategyInterface The upload strategy
     */
    public function moveTo($target)
    {
        if ($this->moved) {
            throw new RuntimeException('The file is already moved.');
        }
        $strategy = $this->getUploadStrategy();
        if (! $target instanceof UploadTargetInterface) {
            $target = new UploadTarget($target);
        }
        if (func_num_args() > 1) {
            $options = func_get_arg(1);
            if (! $options instanceof UploadOptionsInterface) {
                $options = new UploadOptions($options);
            }
            $strategy->setOptions($options);
        }
        $strategy($this, $target);

        $this->moved = true;

        return $strategy;
    }

    /**
     * Sets the client file name.
     *
     * @param string $name The client file name
     *
     * @throws \InvalidArgumentException If the received name of file is
     *                                   not string
     */
    protected function setClientFileName($name)
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException(
                'Invalid file name provided; must be a string.'
            );
        }
        $this->clientFileName = $name;
    }

    /**
     * Sets the temporary filename.
     *
     * @param string $path The path to temporary file
     *
     * @throws \InvalidArgumentException If the received path to file is
     *                                   not string
     */
    protected function setTempName($path)
    {
        if (! is_string($path)) {
            throw new InvalidArgumentException(
                'Invalid file path provided; must be a string.'
            );
        }
        $this->tempName = $path;
    }

    /**
     * Sets the mime type of file.
     *
     * @param string $mediaType The mime type of file, as client its proposed
     *
     * @throws \InvalidArgumentException If the received mime type is
     *                                   not string
     */
    protected function setClientMediaType($mediaType)
    {
        if (! is_string($mediaType)) {
            throw new InvalidArgumentException(
                'Invalid media type provided; must be a string.'
            );
        }

        $this->clientMediaType = $mediaType;
    }

    /**
     * Sets the size of file.
     *
     * @param int $size The size of file
     *
     * @throws \InvalidArgumentException If the received size of file is
     *                                   not integer
     */
    protected function setSize($size)
    {
        if (! is_int($size)) {
            throw new InvalidArgumentException(
                'Invalid file size provided; must be an integer.'
            );
        }
        $this->size = $size;
    }

    /**
     * Sets the error.
     *
     * @param int $error One of PHP's UPLOAD_ERR_XXX constants
     *
     * @throws \InvalidArgumentException If the received error is not one of
     *                                   PHP's UPLOAD_ERR_XXX constants
     */
    protected function setError($error)
    {
        if (! is_int($error) || 0 > $error || 8 < $error) {
            throw new InvalidArgumentException(
                'Invalid error status provided; must be an UPLOAD_ERR_* constant.'
            );
        }
        $this->error = $error;
    }
}
