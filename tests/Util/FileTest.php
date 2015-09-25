<?php
/**
 * Defines the \DominionEnterprises\Util\FileTest class
 */

namespace DominionEnterprises\Util;

use DominionEnterprises\Util\File as F;
use DominionEnterprises\Util\Time as T;

/**
 * @coversDefaultClass \DominionEnterprises\Util\File
 */
final class FileTest extends \PHPUnit_Framework_TestCase
{
    private $topLevelDirPath;
    private $topLevelFilePath;
    private $subLevelDirPath;
    private $subLevelFilePath;

    private $oldErrorReporting;

    public function setup()
    {
        parent::setup();

        $this->oldErrorReporting = error_reporting();

        $this->topLevelDirPath = sys_get_temp_dir() . '/topLevelTempDir';
        $this->topLevelFilePath = "{$this->topLevelDirPath}/topLevelTempFile";
        $this->subLevelDirPath = "{$this->topLevelDirPath}/subLevelTempDir";
        $this->subLevelFilePath = "{$this->subLevelDirPath}/subLevelTempFile";

        $this->deleteTestFiles();
    }

    //this is just for convenience, DO NOT RELY ON IT
    public function tearDown()
    {
        error_reporting($this->oldErrorReporting);

        $this->deleteTestFiles();
    }

    private function deleteTestFiles()
    {
        if (is_dir($this->topLevelDirPath)) {
            chmod($this->topLevelDirPath, 0777);

            if (is_file($this->topLevelFilePath)) {
                unlink($this->topLevelFilePath);
            }

            if (is_dir($this->subLevelDirPath)) {
                if (is_file($this->subLevelFilePath)) {
                    unlink($this->subLevelFilePath);
                }

                rmdir($this->subLevelDirPath);
            }

            rmdir($this->topLevelDirPath);
        }
    }

    /**
     * @test
     * @covers ::deleteDirectoryContents
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $directoryPath is not a string
     */
    public function deleteDirectoryContentsNonStringPath()
    {
        F::deleteDirectoryContents(1);
    }

    /**
     * @test
     * @covers ::deleteDirectoryContents
     * @expectedException \UnexpectedValueException
     */
    public function deleteDirectoryContentsNonExistentPath()
    {
        error_reporting(0);
        F::deleteDirectoryContents('/some/where/that/doesnt/exist');
    }

    /**
     * @test
     * @covers ::deleteDirectoryContents
     */
    public function deleteDirectoryContentsEmpty()
    {
        $this->assertTrue(mkdir($this->topLevelDirPath));

        F::deleteDirectoryContents($this->topLevelDirPath);

        $this->assertTrue(rmdir($this->topLevelDirPath));
    }

    /**
     * @test
     * @covers ::deleteDirectoryContents
     */
    public function deleteDirectoryContentsWithFiles()
    {
        $this->assertTrue(mkdir($this->subLevelDirPath, 0777, true));

        file_put_contents($this->topLevelFilePath, 'hello dolly !');
        file_put_contents($this->subLevelFilePath, 'hello dolly 2!');

        F::deleteDirectoryContents($this->topLevelDirPath);

        $this->assertTrue(rmdir($this->topLevelDirPath));
    }

    /**
     * @test
     * @covers ::deleteDirectoryContents
     * @expectedException \Exception
     * @expectedExceptionCode 2
     */
    public function deleteDirectoryContentsWithProtectedFile()
    {
        $this->assertTrue(mkdir($this->topLevelDirPath));

        file_put_contents($this->topLevelFilePath, 'hello dolly !');

        $this->assertTrue(chmod($this->topLevelDirPath, 0555));

        error_reporting(0);
        F::deleteDirectoryContents($this->topLevelDirPath);
    }

    /**
     * @test
     * @covers ::deleteDirectoryContents
     * @expectedException \Exception
     * @expectedExceptionCode 1
     */
    public function deleteDirectoryContentsWithProtectedDirectory()
    {
        $this->assertTrue(mkdir($this->subLevelDirPath, 0777, true));

        $this->assertTrue(chmod($this->topLevelDirPath, 0555));

        error_reporting(0);
        F::deleteDirectoryContents($this->topLevelDirPath);
    }

    /**
     * @test
     * @covers ::delete
     * @uses \DominionEnterprises\Util::ensure
     */
    public function deleteBasic()
    {
        $this->assertTrue(mkdir($this->topLevelDirPath));
        file_put_contents($this->topLevelFilePath, 'some text');
        F::delete($this->topLevelFilePath);
        $this->assertFalse(file_exists($this->topLevelFilePath));
    }

    /**
     * @test
     * @covers ::delete
     */
    public function deleteNonExistent()
    {
        $this->assertFalse(file_exists('/path/does/not/exist'));
        F::delete('/path/does/not/exist');
    }

    /**
     * @test
     * @covers ::delete
     * @expectedException \Exception
     */
    public function deleteDirectory()
    {
        $this->assertTrue(mkdir($this->topLevelDirPath));
        error_reporting(0);
        F::delete($this->topLevelDirPath);
    }

    /**
     * @test
     * @covers ::delete
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $path is not a string or is whitespace
     */
    public function deleteNonStringPath()
    {
        F::delete(1);
    }

    /**
     * @test
     * @covers ::delete
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $path is not a string or is whitespace
     */
    public function deletePathIsWhitespace()
    {
        F::delete('  ');
    }

    /**
     * Verify behavior of delete() with protected file.
     *
     * @test
     * @covers ::delete
     * @expectedException \Exception
     */
    public function deleteProtectedFile()
    {
        $this->assertTrue(mkdir($this->topLevelDirPath));

        file_put_contents($this->topLevelFilePath, 'hello dolly !');

        $this->assertTrue(chmod($this->topLevelDirPath, 0555));

        error_reporting(0);
        F::delete($this->topLevelDirPath);
    }
}
