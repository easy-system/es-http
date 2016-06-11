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
 * The factory of request method.
 */
class RequestMethodFactory
{
    /**
     * Makes a string with request method.
     * If unable to retrive request method, returns "GET".
     *
     * @param array $server Optional; null by default or empty array means
     *                      global $_SERVER. The source data
     *
     * @return string The request method
     */
    public static function make(array $server = null)
    {
        if (empty($server)) {
            $server = $_SERVER;
        }
        $method = 'GET';
        if (isset($server['REQUEST_METHOD'])) {
            $method = $server['REQUEST_METHOD'];
        }

        return $method;
    }
}
