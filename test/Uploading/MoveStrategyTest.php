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
use Es\Http\Uploading\MoveStrategy;
use Es\Http\Uploading\UploadTarget;
use Es\Http\UploadedFile;

class MoveStrategyTest extends \PHPUnit_Framework_TestCase
{
    const UPLOADED_CONTENT = 'Lorem ipsum dolor sit amet';

    protected $tempDir = '';

    protected $tempFile = '';

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

        $this->tempFile = $this->tempDir . DIRECTORY_SEPARATOR . 'foo.bar';
        $fp = fopen($this->tempFile, 'w+b');
        fwrite($fp, self::UPLOADED_CONTENT);
        fclose($fp);
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
            new \stdClass,
            [],
            100,
        ];
        $return = [];
        foreach($dirs as $dir) {
            $return[] = [$dir];
        }
        return $return;
    }

    /**
     * @dataProvider invalidDirectoryTypeDataProvider
     */
    public function testSetTargetDirectoryRaiseExceptionIfInvalidTypeOfDirReceived($dir)
    {
        $strategy = new MoveStrategy();
        $this->setExpectedException('InvalidArgumentException');
        $strategy->setTargetDirectory($dir);
    }

    public function testGetTargetDirectory()
    {
        $strategy = new MoveStrategy();
        $strategy->setTargetDirectory($this->tempDir);
        $this->assertSame($this->tempDir, $strategy->getTargetDirectory());
    }

    public function invalidPermissionsDataProvider()
    {
        $permissions = [
            true,
            false,
            new \stdClass,
            [],
            'foo',
            0300, // not readable
            0500, // not writable
            0700, // executable
        ];
        $return = [];
        foreach($permissions as $item) {
            $return[] = [$item];
        }
        return $return;
    }

    /**
     * @dataProvider invalidPermissionsDataProvider
     */
    public function testSetFilePermissionsRaiseExceptionIfInvalidPermissionsReceived($permissions)
    {
        $strategy = new MoveStrategy();
        $this->setExpectedException('InvalidArgumentException');
        $strategy->setFilePermissions($permissions);
    }

    public function testGetFilePermissions()
    {
        $strategy = new MoveStrategy();
        $strategy->setFilePermissions(0660);
        $this->assertSame(0660, $strategy->getFilePermissions());
    }

    public function testInvokeDecideOnFailureWhenUploadedFileContainsError()
    {
        $strategy = new MoveStrategy();
        $file     = new UploadedFile(null, null, null, null, 3);
        $target   = new UploadTarget('foo');
        $strategy($file, $target);
        $this->assertSame($strategy::UPLOADED_FILE_CONTAINS_ERROR, $strategy->getOperationError());
    }

    public function testInvokeDecideOnFailureWhenUploadedFileNotContainsTempName()
    {
        $strategy = new MoveStrategy();
        $file     = new UploadedFile();
        $target   = new UploadTarget('foo');
        $strategy($file, $target);
        $this->assertSame($strategy::UPLOADED_FILE_MISSING_TEMPNAME, $strategy->getOperationError());
    }

    public function testInvokeDecideOnFailureWhenTemporaryFileNotExists()
    {
        $strategy = new MoveStrategy();
        $file     = new UploadedFile(null, 'non-existent-file');
        $target   = new UploadTarget('foo');
        $strategy($file, $target);
        $this->assertSame($strategy::MISSING_TEMPORARY_FILE, $strategy->getOperationError());
    }

    public function testInvokeDecideOnFailureWhenTargetDirectoryNotSpecified()
    {
        $strategy = new MoveStrategy();
        $file     = new UploadedFile(null, $this->tempFile);
        $target   = new UploadTarget('foo');
        $strategy($file, $target);
        $this->assertSame($strategy::TARGET_DIR_NOT_SPECIFIED, $strategy->getOperationError());
    }

    public function testInvokeMoveFileOnSuccess()
    {
        $strategy = new MoveStrategy();
        $strategy->setTargetDirectory($this->tempDir);
        $file   = new UploadedFile(null, $this->tempFile);
        $target = new UploadTarget('moved.file');
        $strategy($file, $target);
        $this->assertFalse($strategy->hasOperationError());
        $moved = $this->tempDir . DIRECTORY_SEPARATOR . 'moved.file';
        $this->assertSame(file_get_contents($moved), self::UPLOADED_CONTENT);
    }

    public function testInvokeMoveOnFailure()
    {
        $strategy = new MoveStrategy();
        $strategy->setTargetDirectory($this->tempDir . DIRECTORY_SEPARATOR . 'foo');
        $file   = new UploadedFile(null, $this->tempFile);
        $target = new UploadTarget('moved.file');
        $strategy($file, $target);
        $this->assertTrue($strategy->hasOperationError());
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
