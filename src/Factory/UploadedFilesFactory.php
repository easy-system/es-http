<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http\Factory;

use Es\Http\UploadedFile;
use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;

/**
 * The factory of uploaded files.
 */
class UploadedFilesFactory
{
    /**
     * Makes an array of uploaded files.
     *
     * @param array $files Optional; null by default or empty array means
     *                     global $_FILES. The source data
     *
     * @return array The headers
     */
    public static function make(array $files = null)
    {
        if (null == $files) {
            $files = $_FILES;
        }

        return static::normalize($files);
    }

    /**
     * Normalizes the array.
     *
     * @param array $files The array with specifications of uploaded files
     *
     * @throws \InvalidArgumentException If invalid value present in
     *                                   files specification
     *
     * @return array The normalized array
     */
    protected static function normalize(array $files)
    {
        $normalized = [];
        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;

                continue;
            }

            if (! is_array($value)) {
                throw new InvalidArgumentException(
                    'Invalid value in files specification.'
                );
            }

            if (isset($value['tmp_name'])) {
                $normalized[$key] = static::build($value);

                continue;
            }

            $normalized[$key] = static::normalize($value);
        }

        return $normalized;
    }

    /**
     * Builds the uploaded file from specification.
     *
     * @param array $file The specification of uploaded file or a nested array
     *
     * @return \Es\Http\UploadedFile The uploaded file
     */
    protected static function build(array $file)
    {
        if (is_array($file['tmp_name'])) {
            return static::normalizeNested($file);
        }

        return new UploadedFile(
            $file['name'],
            $file['tmp_name'],
            $file['type'],
            $file['size'],
            $file['error']
        );
    }

    /**
     * Normalizes a nested array.
     *
     * @param array $files The nested array
     *
     * @return array Normalized array
     */
    protected static function normalizeNested(array $files)
    {
        $normalized = [];
        foreach (array_keys($files['tmp_name']) as $key) {
            $spec = [
                'name'     => $files['name'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'type'     => $files['type'][$key],
                'size'     => $files['size'][$key],
                'error'    => $files['error'][$key],
            ];
            $normalized[$key] = static::build($spec);
        }

        return $normalized;
    }
}
