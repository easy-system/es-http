<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http\Test\Factory;

use Es\Http\Factory\InputFactory;

class InputFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function invalidSourcesDataProvider()
    {
        $sources = [
            true,
            false,
            [],
            'foo',
            new \stdClass(),
        ];
        $return = [];
        foreach ($sources as $source) {
            $return[] = [$source];
        }

        return $return;
    }

    /**
     * @dataProvider invalidSourcesDataProvider
     */
    public function testSetSourceRaiseExceptionForInvalidSourceType($source)
    {
        $this->setExpectedException('InvalidArgumentException');
        InputFactory::setSource($source);
    }

    public function testGetSourceReturnsReceivedSource()
    {
        $source = fopen('php://temp', 'w+b');
        InputFactory::setSource($source);
        $this->assertSame($source, InputFactory::getSource());
    }

    public function testGetSourceCreateSource()
    {
        InputFactory::setSource(null);
        $source = InputFactory::getSource();
        $this->assertTrue(is_resource($source));
    }

    public function testMakeCreateStreamWithDataOfSource()
    {
        $content = 'Lorem ipsum dolor sit amet';
        $source  = fopen('php://temp', 'w+b');
        fwrite($source, $content);

        InputFactory::setSource($source);
        $stream = InputFactory::make();
        $this->assertInstanceOf('Psr\Http\Message\StreamInterface', $stream);
        $this->assertSame($content, $stream->getContents());
    }
}
