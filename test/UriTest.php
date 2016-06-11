<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http\Test;

use Es\Http\Uri;

class UriTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $uri = new Uri('http://user:pass@local.example.com:8080/foo?bar=baz#quz');

        $this->assertSame($uri->getScheme(),    'http');
        $this->assertSame($uri->getUserInfo(),  'user:pass');
        $this->assertSame($uri->getHost(),      'local.example.com');
        $this->assertSame($uri->getPort(),      8080);
        $this->assertSame($uri->getAuthority(), 'user:pass@local.example.com:8080');
        $this->assertSame($uri->getPath(),      '/foo');
        $this->assertSame($uri->getQuery(),     'bar=baz');
        $this->assertSame($uri->getFragment(),  'quz');
    }

    public function invalidUriDataProvider()
    {
        return [
            [true],
            [false],
            [100],
            [[]],
            [new \stdClass()]
        ];
    }

    /**
     * @dataProvider invalidUriDataProvider
     */
    public function testInvalidUriRaiseException($uri)
    {
        $this->setExpectedException('InvalidArgumentException');
        $err = new Uri($uri);
    }

    /**
     * @see http://www.ietf.org/rfc/rfc3986
     *
     * #section-3.3
     * ...URI producing applications often use the reserved characters allowed
     * in a segment to delimit scheme-specific or dereference-handler-specific
     * subcomponents.
     * For example, one URI producer might use a segment such as "name;v=1.1"
     * to indicate a reference to version 1.1 of "name"...
     *
     * #section-3.4
     * ...However, as query components are often used to carry identifying
     * information in the form of "key=value" pairs and one frequently used
     * value is a reference to another URI, it is sometimes better for usability
     * to avoid percent-encoding those characters...
     */
    public function testToString()
    {
        $urls = [
            'http://user:pass@local.example.com:8080/foo?bar=baz#quz',
            'http://local.example.com/foo?return=\'http://local.example.com:8080\'',
            'https://local.example.com/login/\'https://local.example.com/admin\'',
            'http://local.example.com/blog#news=last[month&tag=sport]',
            '../../book/catalog.xml',
            // mailto
            'mailto:someone@example.com,someoneelse@example.com',
            // file
            'file:///C:/test.html',
            'file://192.168.0.100/home/test.html',
            // ftp
            'ftp://guest:qwerty@local.example.com/readme.txt',
            // relative address with schema
            'https:/foo/bar',
            // IPv6 see https://www.ietf.org/rfc/rfc2732.txt
            'http://[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:8080/index.html',
            'http://[1080:0:0:0:8:800:200C:417A]/index.html',
        ];
        foreach ($urls as $url) {
            $uri = new Uri($url);
            $this->assertSame($url, (string) $uri);
        }
    }

    public function testToStringRemoveSchemaIfSchemaIsHttpAndPathIsRelative()
    {
        $uri = new Uri('http:/foo/bar');
        $this->assertSame('/foo/bar', (string) $uri);
        //
        $uri = new Uri('http:index.php?foo=bar');
        $this->assertSame('index.php?foo=bar', (string) $uri);
    }

    public function testEncodeDecode()
    {
        $url     = 'http://local.example.com/blog-$123?foo={5}#news=last[month&tag=sport]';
        $uri     = new Uri();
        $encoded = $uri->encode($url);
        $this->assertSame($encoded, $uri->encode($encoded));
        $this->assertSame($url, $uri->decode($encoded));
    }

    public function testWithScheme()
    {
        $uri = new Uri('http://user:pass@local.example.com:8080/foo?bar=baz#quz');
        $new = $uri->withScheme('https');
        $this->assertNotSame($uri, $new);
        $this->assertSame($new->getScheme(), 'https');
        $this->assertSame(
            'https://user:pass@local.example.com:8080/foo?bar=baz#quz',
            (string) $new
        );
        //
        $uri = new Uri('http://user:pass@local.example.com/img/image.jpg');
        $new = $uri->withScheme('ftp');
        $this->assertNotSame($uri, $new);
        $this->assertSame($new->getScheme(), 'ftp');
        $this->assertSame(
            'ftp://user:pass@local.example.com/img/image.jpg',
            (string) $new
        );
        //
        $uri = new Uri();
        $new = $uri->withScheme('HTTPS://');
        $this->assertSame('https', $new->getScheme());
    }

    public function testGetScheme()
    {
        $uri = new Uri();
        $this->assertSame('', $uri->getScheme());
    }

    public function testWithUserInfoWithUser()
    {
        $uri = new Uri('http://user:pass@local.example.com:8080/foo?bar=baz#quz');
        $new = $uri->withUserInfo('Damon');
        $this->assertNotSame($uri, $new);
        $this->assertSame('Damon', $new->getUserInfo());
        $this->assertSame(
            'http://Damon@local.example.com:8080/foo?bar=baz#quz',
            (string) $new
        );
    }

    public function testWithUserInfoWithUserAndPassword()
    {
        $uri = new Uri('ftp://guest:qwerty@local.example.com/readme.txt');
        $new = $uri->withUserInfo('anonymous', 'damon.easy.system@gmail.com');
        $this->assertNotSame($uri, $new);
        $this->assertSame($new->getUserInfo(), 'anonymous:damon.easy.system@gmail.com');
        $this->assertSame(
            'ftp://anonymous:damon.easy.system@gmail.com@local.example.com/readme.txt',
            (string) $new
        );
    }

    public function testWithHost()
    {
        $uri = new Uri('http://user:pass@local.example.com:8080/foo?bar=baz#quz');
        $new = $uri->withHost('www.example.com');
        $this->assertNotSame($uri, $new);
        $this->assertSame($new->getHost(), 'www.example.com');
        $this->assertSame(
            'http://user:pass@www.example.com:8080/foo?bar=baz#quz',
            (string) $new
        );
        //
        $uri = new Uri('file:///C:/test.html');
        $this->assertSame($uri->getHost(), 'localhost');

        $new = $uri->withHost('192.168.0.100');
        $this->assertNotSame($uri, $new);
        $this->assertSame($new->getHost(), '192.168.0.100');
        $this->assertSame(
            'file://192.168.0.100/C:/test.html',
            (string) $new
        );
    }

    public function testWithPort()
    {
        $uri = new Uri('http://user:pass@local.example.com:8080/foo?bar=baz#quz');
        $new = $uri->withPort(8081);
        $this->assertNotSame($uri, $new);
        $this->assertSame($new->getPort(), 8081);
        $this->assertSame(
            'http://user:pass@local.example.com:8081/foo?bar=baz#quz',
            (string) $new
        );
    }

    public function testWithPortNullResetPort()
    {
        $uri = new Uri('http://user:pass@local.example.com:8080/foo?bar=baz#quz');
        $this->assertSame($uri->getPort(), 8080);
        $new = $uri->withPort(null);
        $this->assertNull($new->getPort());
    }

    public function testWithPortThrowExceptionForInvalidPort()
    {
        $uri = new Uri('http://user:pass@local.example.com:8080/foo?bar=baz#quz');
        $this->setExpectedException('InvalidArgumentException');
        $new = $uri->withPort(false);
    }

    public function testGetPort()
    {
        $uri = new Uri('http://user:pass@local.example.com');
        $this->assertNull($uri->getPort());
        //
        $uri = new Uri('http://user:pass@local.example.com:80');
        $this->assertNull($uri->getPort());
        //
        $uri = new Uri('http://user:pass@local.example.com:8080');
        $this->assertSame($uri->getPort(), 8080);
    }

    public function testGetStandartPort()
    {
        $uri = new Uri();
        //
        $new = $uri->withScheme('http');
        $this->assertSame($new->getStandartPort(), 80);
        //
        $new = $uri->withScheme('https');
        $this->assertSame($new->getStandartPort(), 443);
        //
        $new = $uri->withScheme('ftp');
        $this->assertSame($new->getStandartPort(), 21);
    }

    public function testIsPortValid()
    {
        $uri          = new Uri();
        $invalidPorts = [
            null,
            false,
            true,
            0,
            new \stdClass(),
            [],
            'string',
        ];
        $validPort = rand(2, 65534);
        foreach ($invalidPorts as $item) {
            $this->assertFalse($uri->isPortValid($item));
        }
        $this->assertTrue($uri->isPortValid($validPort));
    }

    public function testGetAuthority()
    {
        $uri = new Uri('192.168.0.100');
        $this->assertSame($uri->getAuthority(), '');
        //
        $uri = new Uri('/192.168.0.100');
        $this->assertSame($uri->getAuthority(), '');
        //
        $uri = new Uri('//192.168.0.100');
        $this->assertSame($uri->getAuthority(), '192.168.0.100');
        //
        $uri = new Uri('//guest:querty@192.168.0.100:8080');
        $this->assertSame($uri->getAuthority(), 'guest:querty@192.168.0.100:8080');
        // standart port
        $uri = new Uri('http://guest:querty@192.168.0.100:80');
        $this->assertSame($uri->getAuthority(), 'guest:querty@192.168.0.100');
    }

    public function testWithPath()
    {
        $uri = new Uri('mailto:someone@example.com');
        $new = $uri->withPath('someelse@example.com');
        $this->assertNotSame($uri, $new);
        $this->assertSame($new->getPath(), 'someelse@example.com');
        $this->assertSame(
            'mailto:someelse@example.com',
            (string) $new
        );
        //
        $uri = new Uri('http://user:pass@local.example.com:8080/foo?bar=baz#quz');
        $new = $uri->withPath('bat');
        $this->assertNotSame($uri, $new);
        $this->assertSame($new->getPath(), 'bat');
        $this->assertSame(
            'http://user:pass@local.example.com:8080/bat?bar=baz#quz',
            (string) $new
        );
    }

    public function testWithPathThrowExceptionWithQuery()
    {
        $uri = new Uri();
        $this->setExpectedException('InvalidArgumentException');
        $new = $uri->withPath('/foo/bar?baz=bat');
    }

    public function testWithPathThrowExceptionWithFragment()
    {
        $uri = new Uri();
        $this->setExpectedException('InvalidArgumentException');
        $new = $uri->withPath('/foo/bar#baz');
    }

    public function testGetPath()
    {
        $uri = new Uri();
        // host-relative path
        $new = $uri->withPath('/foo');
        $this->assertSame('/foo', $new->getPath());
        // location-relative path
        $new = $uri->withPath('../bar/baz');
        $this->assertSame('../bar/baz', $new->getPath());
    }

    public function testWithQuery()
    {
        $uri = new Uri('http://user:pass@local.example.com:8080/foo?bar=baz#quz');
        //
        $new = $uri->withQuery('bar[]=baz&bar[]=bat');
        $this->assertNotSame($uri, $new);
        $this->assertSame($new->getQuery(), 'bar[]=baz&bar[]=bat');
        $this->assertSame(
            'http://user:pass@local.example.com:8080/foo?bar[]=baz&bar[]=bat#quz',
            (string) $new
        );
        // clean the leading character "?"
        $new = $uri->withQuery('?foo');
        $this->assertSame('foo', $new->getQuery());
    }

    public function testGetQuery()
    {
        $uri = new Uri();
        $this->assertSame('', $uri->getQuery());
    }

    public function testWithQueryThrowExceptionWithFragment()
    {
        $uri = new Uri();
        $this->setExpectedException('InvalidArgumentException');
        $new = $uri->withQuery('/foo/bar#baz');
    }

    public function testWithFragment()
    {
        $uri = new Uri('http://user:pass@local.example.com:8080/foo?bar=baz#quz');
        //
        $new = $uri->withFragment('qwerty');
        $this->assertNotSame($uri, $new);
        $this->assertSame($new->getFragment(), 'qwerty');
        $this->assertSame(
            'http://user:pass@local.example.com:8080/foo?bar=baz#qwerty',
            (string) $new
        );
        // clean the leading character "#"
        $new = $uri->withFragment('#foo');
        $this->assertSame('foo', $new->getFragment());
    }

    public function testGetFragment()
    {
        $uri = new Uri();
        $this->assertSame('', $uri->getFragment());
    }
}
