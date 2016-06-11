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

use Es\Http\Uri;

/**
 * The Uri factory.
 */
class UriFactory
{
    /**
     * Makes the instance of Es\Http\Uri.
     *
     * @param array $server Optional; null by default or empty array means
     *                      global $_SERVER. The source data
     *
     * @return \Es\Http\Uri The instance of Uri
     */
    public static function make(array $server = null)
    {
        $scheme = UriSchemeFactory::make($server);
        $host   = UriHostFactory::make($server);
        $port   = UriPortFactory::make($server);
        $path   = UriPathFactory::make($server);
        $query  = UriQueryFactory::make($server);

        $url = '';
        if ($host) {
            $url .= $scheme . '://' . $host . ':' . $port;
            $path = '/' . ltrim($path, '/');
        }
        $url .= $path;

        if ($query) {
            $url .= '?' . $query;
        }

        $uri = new Uri($url);

        if ($host) {
            return $uri;
        }

        return $uri->withScheme($scheme)->withPort($port);
    }
}
