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

use Es\Http\ServerRequest;

/**
 * The factory of Server Request.
 */
class ServerRequestFactory
{
    /**
     * Makes the instance of Server Request.
     *
     * @param null|array $serverParams  Optional; the server parameters
     * @param null|array $cookieParams  Optional; the cookies, if any
     * @param null|array $queryParams   Optional; the query parameters, if any
     * @param null|array $uploadedFiles Optional; the uploaded files, if any
     * @param null|array $attributes    Optional; the application-specified attributes
     *
     * <!-- -->
     * @param null|array|object $parsedBody Optional; the parsed body
     *
     * <!-- -->
     * @param null|string|resource|\Psr\Http\Message\StreamInterface $body Optional; the body
     *
     * <!-- -->
     * @param null|array $headers Optional; the headers
     *
     * <!-- -->
     * @param null|string|\Psr\Http\Message\UriInterface $uri Optional; the URI
     *
     * <!-- -->
     * @param null|string $method   Optional; the request method
     * @param null|string $protocol Optional; the HTTP protocol version
     */
    public static function make(
        array $serverParams = null,
        array $cookieParams = null,
        array $queryParams = null,
        array $uploadedFiles = null,
        array $attributes = null,
        $parsedBody = null,
        $body = null,
        array $headers = null,
        $uri = null,
        $method = null,
        $protocol = null
    ) {
        $cookies = $cookieParams ? $cookieParams : $_COOKIE;
        $server  = $serverParams ? $serverParams : $_SERVER;

        $files = UploadedFilesFactory::make($uploadedFiles);

        $attributes = array_merge((array) $attributes, $_ENV);

        $query      = $queryParams ? $queryParams : $_GET;
        $parsedBody = $parsedBody  ? $parsedBody  : $_POST;

        $body     = $body     ? $body     : InputFactory::make();
        $uri      = $uri      ? $uri      : UriFactory::make($server);
        $headers  = $headers  ? $headers  : HeadersFactory::make($server);
        $method   = $method   ? $method   : RequestMethodFactory::make($server);
        $protocol = $protocol ? $protocol : RequestProtocolFactory::getVersion($server);

        return new ServerRequest(
            $server,
            $cookies,
            $query,
            $files,
            $attributes,
            $parsedBody,
            $body,
            $headers,
            $uri,
            $method,
            $protocol
        );
    }
}
