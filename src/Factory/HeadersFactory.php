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
 * The factory of headers.
 */
class HeadersFactory
{
    /**
     * Makes an array of headers.
     *
     * @param array $server Optional; null by default or empty array means
     *                      global $_SERVER. The source data
     *
     * @return array The headers
     */
    public static function make(array $server = null)
    {
        if (empty($server)) {
            $server = $_SERVER;
        }
        $headers = [];
        $content = [
            'CONTENT_TYPE'   => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
            'CONTENT_MD5'    => 'Content-Md5',
        ];
        foreach ($server as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $key = substr($key, 5);
                if (! isset($content[$key]) || ! isset($server[$key])) {
                    $key = str_replace('_', ' ', $key);
                    $key = ucwords(strtolower($key));
                    $key = str_replace(' ', '-', $key);

                    $headers[$key] = $value;
                }
            } elseif (isset($content[$key])) {
                $headers[$content[$key]] = $value;
            }
        }

        return $headers;
    }
}
