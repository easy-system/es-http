<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Es\Http\Factory;

use UnexpectedValueException;

/**
 * The factory of request protocol parameters.
 */
class RequestProtocolFactory
{
    /**
     * Gets the version of request protocol.
     *
     * @param array $server Optional; null by default or empty array means
     *                      global $_SERVER. The source data
     *
     * @return string The version of request protocol
     */
    public static function getVersion(array $server = null)
    {
        $protocol = static::make($server);

        if (! preg_match('#\A(?:HTTP/)?(?P<version>\d{1}\.\d+)\Z#', $protocol, $matches)) {
            throw new UnexpectedValueException(sprintf(
                'Unrecognized protocol version "%s".',
                $server['SERVER_PROTOCOL']
            ));
        }

        return $matches['version'];
    }

    /**
     * Makes the string, that contains the request protocol.
     *
     * @param array $server Optional; null by default or empty array means
     *                      global $_SERVER. The source data
     *
     * @return string The request protocol
     */
    public static function make(array $server = null)
    {
        if (empty($server)) {
            $server = $_SERVER;
        }

        if (isset($server['SERVER_PROTOCOL'])) {
            return $server['SERVER_PROTOCOL'];
        }

        return 'HTTP/1.1';
    }
}
