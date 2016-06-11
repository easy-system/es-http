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
 * Provides file movement to a specific directory.
 */
class MoveStrategy extends AbstractUploadStrategy
{
    /**#@+
     * @const string The error
     */
    const UPLOADED_FILE_CONTAINS_ERROR   = 'uploaded-file-contains-error';
    const UPLOADED_FILE_MISSING_TEMPNAME = 'uploaded-file-missing-tempname';
    const MISSING_TEMPORARY_FILE         = 'missing-temporary-file';
    const TARGET_DIR_NOT_SPECIFIED       = 'target-dir-not-specified';
    const MOVEMENT_FAILED                = 'movement-failed';
    /**#@-*/

    /**
     * The array containing the errors and thereof description.
     *
     * @var array
     */
    protected $errors = [
        self::UPLOADED_FILE_CONTAINS_ERROR   => 'The uploaded file contains errors.',
        self::UPLOADED_FILE_MISSING_TEMPNAME => 'Missing tempname of uploaded file.',
        self::MISSING_TEMPORARY_FILE         => 'The temporary file not exists or is not readable.',
        self::TARGET_DIR_NOT_SPECIFIED       => 'The target directory to upload is not specified.',
        self::MOVEMENT_FAILED                => 'Move the uploaded file failed.',
    ];

    /**
     * The permissions of files in the new location.
     *
     * @var int
     */
    protected $filePermissions = 0600;

    /**
     * The target directory to upload.
     *
     * @var null|string
     */
    protected $targetDirectory;

    /**
     * Sets the target directory to move.
     *
     * If the upload options contains the "target_directory" key, this method
     * will be called automatically.
     *
     * @param string $dir The target directory to move
     *
     * @throws \InvalidArgumentException If the provided directory is not
     *                                   non-empty string
     */
    public function setTargetDirectory($dir)
    {
        if (! is_string($dir) || empty($dir)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid target directory provided; must be a non-empty '
                . 'string, "%s" received.',
                is_object($dir) ? get_class($dir) : gettype($dir)
            ));
        }
        $this->targetDirectory = $dir;
    }

    /**
     * Gets the target directory to move.
     *
     * @return null|string The target directory to move
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    /**
     * Sets the file permissions.
     *
     * If the upload options contains the "file_permissions" key, this method
     * will be called automatically.
     *
     * @param int $permissions The permissions of files in the new location
     *
     * @throws \InvalidArgumentException
     *
     * - If specified permissions are non integer
     * - If specified permissions are not readable
     * - If specified permissions are not writable
     * - If specified permissions are executable
     */
    public function setFilePermissions($permissions)
    {
        if (! is_int($permissions)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid file permissins provided; must be an '
                . 'integer, "%s" received.',
                is_object($permissions) ? get_class($permissions)
                                        : gettype($permissions)
            ));
        }
        if (! (0b100000000 & $permissions)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid file permissions "%s" provided. '
                . 'Files will not available for reading.',
                decoct($permissions)
            ));
        }
        if (! (0b010000000 & $permissions)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid file permissions "%s" provided. '
                . 'Files will not available for writing.',
                decoct($permissions)
            ));
        }
        if (0b001000000 & $permissions) {
            throw new InvalidArgumentException(sprintf(
                'Invalid file permissions "%s" provided. '
                . 'Files will be executable.',
                decoct($permissions)
            ));
        }
        $this->filePermissions = $permissions;
    }

    /**
     * Gets the file permissions.
     *
     * @return int The permissions of files in the new location
     */
    public function getFilePermissions()
    {
        return $this->filePermissions;
    }

    /**
     * Moves uploaded file to a specific directory.
     *
     * @param \Es\Http\UploadedFileInterface $file   The value object, that
     *                                               represents uploaded file
     * @param UploadTargetInterface          $target The target, that represents
     *                                               the new file name
     */
    public function __invoke(UploadedFileInterface $file, UploadTargetInterface $target)
    {
        if ($file->getError() > 0) {
            $this->decideOnFailure(static::UPLOADED_FILE_CONTAINS_ERROR);

            return;
        }
        $source = $file->getTempName();
        if (! $source) {
            $this->decideOnFailure(static::UPLOADED_FILE_MISSING_TEMPNAME);

            return;
        }
        if (! is_readable($source)) {
            $this->decideOnFailure(static::MISSING_TEMPORARY_FILE);

            return;
        }
        $dir = $this->getTargetDirectory();
        if (! $dir) {
            $this->decideOnFailure(static::TARGET_DIR_NOT_SPECIFIED);

            return;
        }
        $destination = $dir . DIRECTORY_SEPARATOR . $target;
        set_error_handler(function ($errno, $errstr) {
            throw new RuntimeException($errstr);
        });
        try {
            $sapi = PHP_SAPI;
            switch (true) {
                case empty($sapi) || 0 === strpos($sapi, 'cli'):
                    rename($source, $destination);
                    break;
            // @codeCoverageIgnoreStart
                default:
                    move_uploaded_file($source, $destination);
            }
            // @codeCoverageIgnoreEnd
            chmod($destination, $this->filePermissions);
        } catch (RuntimeException $ex) {
            restore_error_handler();
            $this->errors[static::MOVEMENT_FAILED] = $ex->getMessage();
            $this->decideOnFailure(static::MOVEMENT_FAILED);

            return;
        }
        restore_error_handler();
        $this->decideOnSuccess();
    }
}
