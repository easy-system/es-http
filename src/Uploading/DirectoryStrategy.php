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
 * Checks whether the target directory exists, creates it if necessary.
 * Checks the directory permissions, changes it if necessary.
 */
class DirectoryStrategy extends AbstractUploadStrategy
{
    /**#@+
     * @const string The error
     */
    const UPLOADED_FILE_CONTAINS_ERROR = 'uploaded-file-contains-error';
    const TARGET_DIR_NOT_SPECIFIED     = 'target-dir-not-specified';
    const CREATE_DIRECTORY_FAILED      = 'create-directory-failed';
    const DIRECTORY_NOT_READABLE       = 'directory-not-readable';
    const DIRECTORY_NOT_WRITABLE       = 'directory-not-writable';
    /**#@-*/

    /**
     * The array containing the errors and thereof description.
     *
     * @var array
     */
    protected $errors = [
        self::UPLOADED_FILE_CONTAINS_ERROR => 'The uploaded file contains errors.',
        self::TARGET_DIR_NOT_SPECIFIED     => 'The target directory to upload is not specified.',
        self::CREATE_DIRECTORY_FAILED      => 'Failed to create directory.',
        self::DIRECTORY_NOT_READABLE       => 'The target directory is not readable.',
        self::DIRECTORY_NOT_WRITABLE       => 'The target directory is not writable.',
    ];

    /**
     * The permissions of new directories.
     *
     * @var int
     */
    protected $dirPermissions = 0700;

    /**
     * The target directory to upload.
     *
     * @var null|string
     */
    protected $targetDirectory;

    /**
     * Sets the target directory.
     *
     * If the upload options contains the "target_directory" key, this method
     * will be called automatically.
     *
     * @param string $dir The target directory to upload
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
     * Gets the target directory.
     *
     * @return null|string The target directory
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    /**
     * Sets the permissions of new directories.
     *
     * If the upload options contains the "dir_permissions" key, this method
     * will be called automatically.
     *
     * @param int $permissions The permissions of new directories
     *
     * @throws \InvalidArgumentException
     *
     * - If specified permissions are non integer
     * - If specified permissions are not readable
     * - If specified permissions are not writable
     * - If specified permissions makes the content unreadable
     */
    public function setDirPermissions($permissions)
    {
        if (! is_int($permissions)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid directory permissins provided; must be an '
                . 'integer, "%s" received.',
                is_object($permissions) ? get_class($permissions)
                                        : gettype($permissions)
            ));
        }
        if (! (0b100000000 & $permissions)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid permissions "%s" for directories. '
                . 'Directories will not available for reading.',
                decoct($permissions)
            ));
        }
        if (! (0b010000000 & $permissions)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid permissions "%s" for directories. '
                . 'Directories will not available for writing.',
                decoct($permissions)
            ));
        }
        if (! (0b001000000 & $permissions)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid permissions "%s" for directories. '
                . 'The content of directories will not available.',
                decoct($permissions)
            ));
        }
        $this->dirPermissions = $permissions;
    }

    /**
     * Gets the directory permissions.
     *
     * @return int The permissions
     */
    public function getDirPermissions()
    {
        return $this->dirPermissions;
    }

    /**
     * Checks whether the target directory exists, creates it if necessary.
     * Checks the directory permissions, changes it if necessary.
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
        $dir = $this->getTargetDirectory();
        if (! $dir) {
            $this->decideOnFailure(static::TARGET_DIR_NOT_SPECIFIED);

            return;
        }
        set_error_handler(function ($errno, $errstr) {
            throw new RuntimeException($errstr);
        });
        try {
            if (! file_exists($dir) || ! is_dir($dir)) {
                mkdir($dir, $this->getDirPermissions(), true);
            }
        } catch (RuntimeException $ex) {
            restore_error_handler();
            $this->errors[static::CREATE_DIRECTORY_FAILED] = $ex->getMessage();
            $this->decideOnFailure(static::CREATE_DIRECTORY_FAILED);

            return;
        }
        restore_error_handler();
        if (! is_readable($dir)) {
            $this->decideOnFailure(static::DIRECTORY_NOT_READABLE);

            return;
        }
        if (! is_writable($dir)) {
            $this->decideOnFailure(static::DIRECTORY_NOT_WRITABLE);

            return;
        }
        $this->decideOnSuccess();
    }
}
