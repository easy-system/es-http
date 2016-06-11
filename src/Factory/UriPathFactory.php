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

/**
 * The factory of Uri path.
 */
class UriPathFactory
{
    /**
     * Makes a string with Uri path.
     *
     * @param array $server Optional; null by default or empty array means
     *                      global $_SERVER. The source data
     *
     * @return string|null Returns the Uri path if any, null otherwise
     */
    public static function make(array $server = null)
    {
        if (empty($server)) {
            $server = $_SERVER;
        }

        if (isset($server['REQUEST_URI'])) {
            return parse_url($server['REQUEST_URI'], PHP_URL_PATH);
        }
    }
}
