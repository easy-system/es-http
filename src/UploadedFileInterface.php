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

use Es\Http\Uploading\UploadStrategyInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface as BaseInterface;

/**
 * Value object representing a file uploaded through an HTTP request.
 *
 * Because it is value object, all properties (as temp_name) must be available.
 *
 * Since the Uploaded File is an object-value, and besides the Uploading itself
 * is specific to each application, the mechanism of uploads is represented as a
 * separate interface of upload strategy.
 */
interface UploadedFileInterface extends BaseInterface
{
    /**
     * Retrive the temporary filename of the file in which the uploaded file was
     * stored on the server.
     *
     * @return null|string The path to temporary file or null if none
     *                     was provided
     */
    public function getTempName();

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
    public function setUploadStrategy(UploadStrategyInterface $strategy);

    /**
     * Retrieve a upload strategy.
     *
     * If the upload strategy was not set, returns the simple default strategy.
     *
     * @return \Es\Http\Uploading\UploadStrategyInterface The upload strategy
     */
    public function getUploadStrategy();

    /**
     * Sets a stream representing the uploaded file.
     *
     * Can be used for support for the HTTP PUT method.
     *
     * @link http://php.net/manual/en/features.file-upload.put-method.php
     *
     * @param \Psr\Http\Message\StreamInterface $stream Stream representation
     *                                                  of the uploaded file
     */
    public function setStream(StreamInterface $stream);
}
