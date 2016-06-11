<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http\Response;

function &data()
{
    static $data;

    return $data;
}

function &headers()
{
    static $headers;

    return $headers;
}

function send_headers($flag = true)
{
    $headers = &headers();
    $headers = (bool) $flag;
}

function headers_sent()
{
    return (bool) headers();
}

function ob_get_level($level = 0)
{
    static $level;
    if (null === $level) {
        $level = 3;
    }
    $level -= $level > 0 ? 1 : 0;

    return $level;
}

function ob_end_flush()
{
    return true;
}

function header($string)
{
    $headers   = &data();
    $headers[] = $string;
}

function get_headers()
{
    $headers = &data();

    return $headers;
}

function reset_headers()
{
    $headers = &data();
    $headers = [];
}
