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

use DomainException;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * The representation of abstract stream wrapper.
 */
abstract class AbstractStream
{
    /**
     * The system protocol.
     *
     * @const string
     */
    const PROTOCOL = 'system';

    /**
     * The stream context.
     *
     * @var resource
     */
    public $context;

    /**
     * The wrapped resource.
     *
     * @var resource
     */
    protected $resource;

    /**
     * Makes new stream instance of current stream class.
     *
     * @param string|object|resource $resource Optional;
     *
     * - the string as a body of new resource
     * - or an object which can be converted to a string (the instance of
     *   AbstractStream returns without changes)
     * - or the resource to wrap
     * @param string $mode Optional; "w+b" by default. The type of access to
     *                     the stream if the access type is not specified
     *                     for a resource
     *
     * @throws \InvalidArgumentException If the type of the specified resource
     *                                   is not acceptable
     *
     * @return self
     */
    public static function make($resource = '', $mode = 'w+b')
    {
        switch (gettype($resource)) {
            case 'resource':
                return (new static(false))->setResource($resource, $mode);
            case 'string':
                $stream = fopen('php://temp', $mode);
                if ($resource !== '') {
                    fwrite($stream, $resource);
                    fseek($stream, 0);
                }

                return (new static(false))->setResource($stream, $mode);
            case 'object':
                if ($resource instanceof self) {
                    return $resource;
                } elseif (method_exists($resource, '__toString')) {
                    $resource = (string) $resource;

                    return static::make($resource, $mode);
                }
            default:
                throw new InvalidArgumentException(sprintf(
                    'Invalid resource "%s" provided.',
                    is_object($resource) ? get_class($resource)
                                         : gettype($resource)
                ));
        }
    }

    /**
     * Makes new resource from instance of current stream class.
     *
     * @param string|resource $resource Optional; "php://temp" by default. The
     *                                  resource wrapper reference as
     *                                  "scheme://target" or resource to wrap
     * @param string          $mode     Optional; "w+b" by default. The type of
     *                                  access to the stream if the access type
     *                                  is not specified for a resource
     *
     * @throws \InvalidArgumentException If failed to create resource from
     *                                   received resource
     *
     * @return resource New resource
     */
    public static function fopen($resource = 'php://temp', $mode = 'w+b')
    {
        static::register();

        $context = stream_context_create(
            [static::PROTOCOL => ['resource' => $resource]]
        );
        set_error_handler(function () {
            throw new Exception('Failed to open stream.');
        });
        try {
            $fp = fopen(static::PROTOCOL . '://', $mode, false, $context);
        } catch (Exception $ex) {
            restore_error_handler();
            throw new InvalidArgumentException(sprintf(
                'Failed to create resource from "%s".',
                is_resource($resource) ? get_resource_type($resource)
                                       : gettype($resource)
            ), null, $ex);
        }
        restore_error_handler();

        return $fp;
    }

    /**
     * Register the current class as wrapper for class-specified protocol.
     *
     * @param int $flags Optional; null by default means "STREAM_IS_URL". The
     *                   specification of protocol type - URL protocol or local
     *
     * @return bool Returns true on success, false otherwise
     */
    public static function register($flags = null)
    {
        $protocol = static::PROTOCOL;

        if (static::isRegistered()) {
            return true;
        }
        if (null === $flags) {
            $flags = STREAM_IS_URL;
        }

        return stream_wrapper_register($protocol, static::class, $flags);
    }

    /**
     * Unregister the wrapper for class-specified protocol.
     *
     * @return bool Returns true on success, false otherwise
     */
    public static function unregister()
    {
        return stream_wrapper_unregister(static::PROTOCOL);
    }

    /**
     * Is the stream wrapper registered?
     *
     * @return bool True on success, false otherwise
     */
    public static function isRegistered()
    {
        return in_array(static::PROTOCOL, stream_get_wrappers());
    }

    /**
     * Opens an stream.
     * Uses by PHP to open an stream for specified protocol.
     *
     * @param string $path        Not used. The path as "scheme://target"
     * @param string $mode        Not used. The type of access to the stream
     * @param int    $options     Not used. Holds additional flags set by the
     *                            streams API
     * @param string $openedPath& Not used. The full path of the file/resource
     *                            to be set
     *
     * @return bool Returns true on success, false otherwise
     */
    public function open($path = null, $mode = null, $options = null, &$openedPath = null)
    {
        $context = [];
        if (is_resource($this->context)) {
            $context = stream_context_get_options($this->context);
        }
        $resource = 'php://temp';
        if (isset($context[static::PROTOCOL]['resource'])) {
            $resource = $context[static::PROTOCOL]['resource'];
        }
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
        try {
            $this->setResource($resource, $mode);
        } catch (InvalidArgumentException $ex) {
            return false;
        }

        return true;
    }

    /**
     * Copies the contents of source.
     *
     * If the source is seekable, copies content from beginning. Otherwise
     * copies data from current position.
     *
     * @param string|resource|AbstractStream $source The source to copy
     *
     * @throws \RuntimeException         If the internal resource is not
     *                                   available
     * @throws \InvalidArgumentException
     *
     * - If invalid source provided
     * - If source is not readable
     */
    public function copy($source)
    {
        if (! $this->resource) {
            throw new RuntimeException('No resource available; cannot copy.');
        }
        $resource = null;
        if ($source instanceof self) {
            $resource = $source->getResource();
        } elseif (is_resource($source)) {
            $resource = $source;
        } elseif (is_string($source)) {
            set_error_handler(function () {
                throw new Exception('Failed to open stream.');
            });
            try {
                $resource = fopen($source, 'rb');
            } catch (Exception $ex) {
                restore_error_handler();
                throw new InvalidArgumentException(sprintf(
                    'Invalid source path "%s" specified.',
                    $source
                ));
            }
            restore_error_handler();
        } else {
            throw new InvalidArgumentException(sprintf(
                'Invalid source "%s" provided.',
                is_object($source) ? get_class($source) : gettype($source)
            ));
        }
        $metadata = stream_get_meta_data($resource);
        $mode     = $metadata['mode'];
        if (! (strstr($mode, 'r') || strstr($mode, '+'))) {
            throw new InvalidArgumentException(
                'The received source is not readable.'
            );
        }
        $seekable = $metadata['seekable'];
        if ($seekable) {
            rewind($resource);
        }
        stream_copy_to_stream($resource, $this->resource);
        rewind($this->resource);
        if (is_string($source)) {
            fclose($resource);
        }
    }

    /**
     * Sets the resource to wrapper.
     *
     * @param string|resource $resource Optional; "php://temp" by default. The
     *                                  resource wrapper reference as
     *                                  "scheme://target" or resource to wrap
     * @param string          $mode     Optional; "w+b" by default. The type of
     *                                  access to the stream if the access type
     *                                  is not specified for a resource
     *
     * @throws \DomainException          If resource already set
     * @throws \InvalidArgumentException If the type of the specified resource
     *                                   is not acceptable
     *
     * @return self
     */
    public function setResource($resource = 'php://temp', $mode = 'w+b')
    {
        if (is_resource($this->resource)) {
            throw new DomainException('The resource is already set.');
        }

        if (is_resource($resource)) {
            $this->resource = $resource;
        } elseif (is_string($resource)) {
            set_error_handler(function () {
                throw new Exception('Failed to open stream.');
            }, E_WARNING);
            try {
                $this->resource = fopen($resource, $mode);
            } catch (Exception $ex) {
                restore_error_handler();
                throw new InvalidArgumentException(sprintf(
                    'Invalid path "%s" specified.',
                    $resource
                ));
            }
            restore_error_handler();
        } else {
            throw new InvalidArgumentException(sprintf(
                'Invalid resource "%s" provided.',
                is_object($resource) ? get_class($resource) : gettype($resource)
            ));
        }

        return $this;
    }

    /**
     * Gets the wrapped resource.
     *
     * @return null|resource The wrapped resource if exists or null
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Retrieve information about a resource.
     *
     * @return array|null The statistics of the opened resource if any or null
     */
    public function stat()
    {
        $stat = null;
        if (is_resource($this->resource)) {
            $stat = fstat($this->resource);
        }
        return $stat;
    }

    /**
     * The stub for flushes the output.
     *
     * @return true
     */
    public function flush()
    {
        return true;
    }

    /**
     * Dynamic overloading of methods.
     * Removes "stream_" prefix from method name.
     *
     * @param string $name      The name of method
     * @param array  $arguments The arguments
     *
     * @throws \InvalidArgumentException If method not exists
     *
     * @return mixed
     */
    public function __call($name, $arguments = [])
    {
        if (0 === strpos($name, 'stream_')) {
            $name = substr($name, 7);
        }
        $method = strtolower(str_replace('_', '', $name));

        if (! method_exists($this, $method)) {
            throw new InvalidArgumentException(
                sprintf('Method "%s" doesn\'t exist.', $name)
            );
        }

        return call_user_func_array([$this, $method], $arguments);
    }
}
