<?php
/**
 * This file is part of the "Easy System" package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Damon Smith <damon.easy.system@gmail.com>
 */
namespace Es\Http\Test\Uploading;

use DirectoryIterator;
use Es\Http\UploadedFile;
use Es\Http\Uploading\DirectoryStrategy;
use Es\Http\Uploading\UploadTarget;

class DirectoryStrategyTest extends \PHPUnit_Framework_TestCase
{
    protected $tempDir = '';

    public function setUp()
    {
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'es-http-test';
        if (file_exists($this->tempDir) && is_dir($this->tempDir)) {
            if (! is_writable($this->tempDir)) {
                $this->fail(sprintf(
                    'Temporary directory "%s" already exists and not writable.',
                    $this->tempDir
                ));

                return;
            }
            if (! is_readable($this->tempDir)) {
                $this->fail(sprintf(
                    'Temporary directory "%s" already exists and not readable.',
                    $this->tempDir
                ));

                return;
            }
        } elseif (! @mkdir($this->tempDir, 0700, true)) {
            $this->fail(sprintf(
                'Failed to create temporary directory "%s".',
                $this->tempDir
            ));
        }
    }

    public function tearDown()
    {
        $this->removeRecursive($this->tempDir);
    }

    public function invalidDirectoryTypeDataProvider()
    {
        $dirs = [
            true,
            false,
            new \stdClass(),
            [],
            100,
        ];
        $return = [];
        foreach ($dirs as $dir) {
            $return[] = [$dir];
        }

        return $return;
    }

    /**
     * @dataProvider invalidDirectoryTypeDataProvider
     */
    public function testSetTargetDirectoryRaiseExceptionIfInvalidTypeOfDirReceived($dir)
    {
        $strategy = new DirectoryStrategy();
        $this->setExpectedException('InvalidArgumentException');
        $strategy->setTargetDirectory($dir);
    }

    public function testGetTargetDirectory()
    {
        $strategy = new DirectoryStrategy();
        $strategy->setTargetDirectory('foo');
        $this->assertSame('foo', $strategy->getTargetDirectory());
    }

    public function invalidPermissionsDataProvider()
    {
        $permissions = [
            true,
            false,
            new \stdClass(),
            [],
            'foo',
            0300, // not readable
            0500, // not writable
            0600, // not readable content
        ];
        $return = [];
        foreach ($permissions as $item) {
            $return[] = [$item];
        }

        return $return;
    }

    /**
     * @dataProvider invalidPermissionsDataProvider
     */
    public function testSetDirPermissionsRaiseExceptionIfInvalidPermissionsReceived($permissions)
    {
        $strategy = new DirectoryStrategy();
        $this->setExpectedException('InvalidArgumentException');
        $strategy->setDirPermissions($permissions);
    }

    public function testGetDirPermissions()
    {
        $strategy = new DirectoryStrategy();
        $strategy->setDirPermissions(0770);
        $this->assertSame(0770, $strategy->getDirPermissions());
    }

    public function testInvokeDecideOnFailureWhenUploadedFileContainsError()
    {
        $strategy = new DirectoryStrategy();
        $file     = new UploadedFile(null, null, null, null, 6);
        $target   = new UploadTarget('foo');
        $strategy($file, $target);
        $this->assertSame($strategy::UPLOADED_FILE_CONTAINS_ERROR, $strategy->getOperationError());
    }

    public function testInvokeDecideOnFailureWhenTargetDirectoryNotSpecified()
    {
        $strategy = new DirectoryStrategy();
        $file     = new UploadedFile();
        $target   = new UploadTarget('foo');
        $strategy($file, $target);
        $this->assertSame($strategy::TARGET_DIR_NOT_SPECIFIED, $strategy->getOperationError());
    }

    public function testInvokeDecideOnFailureWhenUnableToCreateDirectory()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }
        $nonWritableDir = $this->tempDir . DIRECTORY_SEPARATOR . 'non-writable-dir';
        if (! file_exists($nonWritableDir)) {
            mkdir($nonWritableDir, 0500, true);
        }
        $targetDir = $nonWritableDir . DIRECTORY_SEPARATOR . 'target-dir';

        $strategy = new DirectoryStrategy();
        $strategy->setTargetDirectory($targetDir);
        $file   = new UploadedFile();
        $target = new UploadTarget('foo');
        $strategy($file, $target);
        $this->assertSame($strategy::CREATE_DIRECTORY_FAILED, $strategy->getOperationError());
    }

    public function testInvokeDecideOnFailureWhenDirectoryExistsButNotWritable()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }
        $nonWritableDir = $this->tempDir . DIRECTORY_SEPARATOR . 'non-writable-dir';
        if (! file_exists($nonWritableDir)) {
            mkdir($nonWritableDir, 0500, true);
        }
        $strategy = new DirectoryStrategy();
        $strategy->setTargetDirectory($nonWritableDir);
        $file   = new UploadedFile();
        $target = new UploadTarget('foo');
        $strategy($file, $target);
        $this->assertSame($strategy::DIRECTORY_NOT_WRITABLE, $strategy->getOperationError());
    }

    public function testInvokeDecideOnFailureWhenDirectoryExistsButNotReadable()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            $this->markTestSkipped('Not testable on windows.');
        }
        $nonReadableDir = $this->tempDir . DIRECTORY_SEPARATOR . 'non-readable-dir';
        if (! file_exists($nonReadableDir)) {
            mkdir($nonReadableDir, 0300, true);
        }
        $strategy = new DirectoryStrategy();
        $strategy->setTargetDirectory($nonReadableDir);
        $file   = new UploadedFile();
        $target = new UploadTarget('foo');
        $strategy($file, $target);
        $this->assertSame($strategy::DIRECTORY_NOT_READABLE, $strategy->getOperationError());
    }

    public function testInvokeOnSuccess()
    {
        $targetDir = $this->tempDir . DIRECTORY_SEPARATOR . 'target-dir';
        $strategy  = new DirectoryStrategy();
        $strategy->setTargetDirectory($targetDir);
        $file   = new UploadedFile();
        $target = new UploadTarget('foo');
        $strategy($file, $target);
        $this->assertFalse($strategy->hasOperationError());
        $this->assertTrue(file_exists($targetDir) && is_dir($targetDir));
        $this->assertTrue(is_readable($targetDir) && is_writable($targetDir));
    }

    protected function removeRecursive($dir)
    {
        if (file_exists($dir)) {
            $dirIt = new DirectoryIterator($dir);
            foreach ($dirIt as $entry) {
                $fname = $entry->getFilename();
                if ($fname == '.' || $fname == '..') {
                    continue;
                }

                if ($entry->isFile()) {
                    unlink($entry->getPathname());
                } else {
                    chmod($entry->getPathname(), 0700);
                    $this->removeRecursive($entry->getPathname());
                }
            }
            rmdir($dir);
        }
    }
}
