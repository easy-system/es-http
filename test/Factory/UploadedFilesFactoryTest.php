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

use Es\Http\Factory\UploadedFilesFactory;
use Es\Http\UploadedFile;

class UploadedFilesFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function invalidSpecificationsDataProvider()
    {
        $specifications = [
            true,
            false,
            null,
            100,
            new \stdClass(),
        ];
        $return = [];
        foreach ($specifications as $spec) {
            $return[] = [$spec];
        }

        return $return;
    }

    /**
     * @dataProvider invalidSpecificationsDataProvider
     */
    public function testRaisesExceptionIfInvalidSpecificationProvided($specification)
    {
        $files = [
            'foo' => $specification,
        ];
        $this->setExpectedException('InvalidArgumentException');
        UploadedFilesFactory::make($files);
    }

    public function testMakeMakesArayWithUploadedFiles()
    {
        $files = [
            'foo' => [
                'name'     => 'bar',
                'type'     => 'bat',
                'tmp_name' => 'baz',
                'error'    => 0,
                'size'     => 100,
            ],
        ];
        $result = UploadedFilesFactory::make($files);
        $this->assertTrue(is_array($result));
        $this->assertTrue(isset($result['foo']));
        $this->assertInstanceOf(UploadedFile::CLASS, $result['foo']);

        $file = $result['foo'];
        $this->assertSame('bar', $file->getClientFileName());
        $this->assertSame('bat', $file->getClientMediaType());
        $this->assertSame('baz', $file->getTempName());
        $this->assertSame(0,     $file->getError());
        $this->assertSame(100,   $file->getSize());
    }

    public function testMakePreserveUploadedFileIfExists()
    {
        $file  = new UploadedFile();
        $files = [
            'foo' => $file,
        ];
        $result = UploadedFilesFactory::make($files);
        $this->assertTrue(is_array($result));
        $this->assertTrue(isset($result['foo']));
        $this->assertSame($file, $result['foo']);
    }

    public function testMakeMakesArrayfromNestedArrays()
    {
        $files = [
            'foo' => [
                'name'     => ['bar' => ['baz' => 'cor']],
                'type'     => ['bar' => ['baz' => 'con']],
                'tmp_name' => ['bar' => ['baz' => 'cot']],
                'error'    => ['bar' => ['baz' => 0]],
                'size'     => ['bar' => ['baz' => 100]],
            ],
        ];
        $result = UploadedFilesFactory::make($files);
        $this->assertTrue(is_array($result));
        $this->assertTrue(isset($result['foo']['bar']['baz']));
        $this->assertInstanceOf(UploadedFile::CLASS, $result['foo']['bar']['baz']);

        $file = $result['foo']['bar']['baz'];
        $this->assertSame('cor', $file->getClientFileName());
        $this->assertSame('con', $file->getClientMediaType());
        $this->assertSame('cot', $file->getTempName());
        $this->assertSame(0,     $file->getError());
        $this->assertSame(100,   $file->getSize());
    }

    public function testMakeMakesWithAlreadyNormalizedArray()
    {
        $files = [
            'foo' => [
                0 => [
                    'name'     => 'foo',
                    'type'     => 'bat',
                    'tmp_name' => 'con',
                    'error'    => 0,
                    'size'     => 100,
                ],
                1 => [
                    'name'     => 'bar',
                    'type'     => 'baz',
                    'tmp_name' => 'com',
                    'error'    => 0,
                    'size'     => 200,
                ],
            ],
        ];

        $result = UploadedFilesFactory::make($files);
        $this->assertTrue(is_array($result));
        $this->assertTrue(isset($result['foo']));
        $this->assertTrue(is_array($result['foo']));
        $this->assertSame(2, count($result['foo']));

        $first = $result['foo'][0];
        $this->assertInstanceOf(UploadedFile::CLASS, $first);
        $this->assertSame('foo', $first->getClientFileName());
        $this->assertSame('bat', $first->getClientMediaType());
        $this->assertSame('con', $first->getTempName());
        $this->assertSame(0,     $first->getError());
        $this->assertSame(100,   $first->getSize());

        $second = $result['foo'][1];
        $this->assertInstanceOf(UploadedFile::CLASS, $second);
        $this->assertSame('bar', $second->getClientFileName());
        $this->assertSame('baz', $second->getClientMediaType());
        $this->assertSame('com', $second->getTempName());
        $this->assertSame(0,     $second->getError());
        $this->assertSame(200,   $second->getSize());
    }

    public function testMakeMakesWithFlattenArrays()
    {
        $files = [
            'foo' => [
                'name'     => ['foo', 'bar'],
                'type'     => ['bat', 'baz'],
                'tmp_name' => ['con', 'com'],
                'error'    => [0, 0],
                'size'     => [100, 200],
            ],
        ];

        $result = UploadedFilesFactory::make($files);
        $this->assertTrue(is_array($result));
        $this->assertTrue(isset($result['foo']));
        $this->assertTrue(is_array($result['foo']));
        $this->assertSame(2, count($result['foo']));

        $first = $result['foo'][0];
        $this->assertInstanceOf(UploadedFile::CLASS, $first);
        $this->assertSame('foo', $first->getClientFileName());
        $this->assertSame('bat', $first->getClientMediaType());
        $this->assertSame('con', $first->getTempName());
        $this->assertSame(0,     $first->getError());
        $this->assertSame(100,   $first->getSize());

        $second = $result['foo'][1];
        $this->assertInstanceOf(UploadedFile::CLASS, $second);
        $this->assertSame('bar', $second->getClientFileName());
        $this->assertSame('baz', $second->getClientMediaType());
        $this->assertSame('com', $second->getTempName());
        $this->assertSame(0,     $second->getError());
        $this->assertSame(200,   $second->getSize());
    }
}
