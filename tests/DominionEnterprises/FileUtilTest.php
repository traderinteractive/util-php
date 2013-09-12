<?php
/**
 * Defines the FileUtilTest class
 */

namespace DominionEnterprises;
use DominionEnterprises\FileUtil as F;
use DominionEnterprises\TimeUtil as T;

/**
 * Test class for \DominionEnterprises\FileUtil.
 */
final class FileUtilTest extends \PHPUnit_Framework_TestCase
{
    private $_topLevelDirPath;
    private $_topLevelFilePath;
    private $_subLevelDirPath;
    private $_subLevelFilePath;

    private $_oldErrorReporting;

    public function setup()
    {
        parent::setup();

        $this->_oldErrorReporting = error_reporting();

        $this->_topLevelDirPath = sys_get_temp_dir() . '/topLevelTempDir';
        $this->_topLevelFilePath = "{$this->_topLevelDirPath}/topLevelTempFile";
        $this->_subLevelDirPath = "{$this->_topLevelDirPath}/subLevelTempDir";
        $this->_subLevelFilePath = "{$this->_subLevelDirPath}/subLevelTempFile";

        $this->deleteTestFiles();
    }

    //this is just for convenience, DO NOT RELY ON IT
    public function tearDown()
    {
        error_reporting($this->_oldErrorReporting);

        $this->deleteTestFiles();
    }

    private function deleteTestFiles()
    {
        if (is_dir($this->_topLevelDirPath)) {
            chmod($this->_topLevelDirPath, 0777);

            if (is_file($this->_topLevelFilePath)) {
                unlink($this->_topLevelFilePath);
            }

            if (is_dir($this->_subLevelDirPath)) {
                if (is_file($this->_subLevelFilePath)) {
                    unlink($this->_subLevelFilePath);
                }

                rmdir($this->_subLevelDirPath);
            }

            rmdir($this->_topLevelDirPath);
        }
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $directoryPath is not a string
     */
    public function deleteDirectoryContents_nonStringPath()
    {
        F::deleteDirectoryContents(1);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage cannot list directory '/some/where/that/doesnt/exist'
     */
    public function deleteDirectoryContents_nonExistentPath()
    {
        error_reporting(0);
        F::deleteDirectoryContents('/some/where/that/doesnt/exist');
    }

    /**
     * @test
     */
    public function deleteDirectoryContents_empty()
    {
        $this->assertTrue(mkdir($this->_topLevelDirPath));

        F::deleteDirectoryContents($this->_topLevelDirPath);

        $this->assertTrue(rmdir($this->_topLevelDirPath));
    }

    /**
     * @test
     */
    public function deleteDirectoryContents_withFiles()
    {
        $this->assertTrue(mkdir($this->_subLevelDirPath, 0777, true));

        file_put_contents($this->_topLevelFilePath, 'hello dolly !');
        file_put_contents($this->_subLevelFilePath, 'hello dolly 2!');

        F::deleteDirectoryContents($this->_topLevelDirPath);

        $this->assertTrue(rmdir($this->_topLevelDirPath));
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionCode 2
     */
    public function deleteDirectoryContents_withProtectedFile()
    {
        $this->assertTrue(mkdir($this->_topLevelDirPath));

        file_put_contents($this->_topLevelFilePath, 'hello dolly !');

        $this->assertTrue(chmod($this->_topLevelDirPath, 0555));

        error_reporting(0);
        F::deleteDirectoryContents($this->_topLevelDirPath);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionCode 1
     */
    public function deleteDirectoryContents_withProtectedDirectory()
    {
        $this->assertTrue(mkdir($this->_subLevelDirPath, 0777, true));

        $this->assertTrue(chmod($this->_topLevelDirPath, 0555));

        error_reporting(0);
        F::deleteDirectoryContents($this->_topLevelDirPath);
    }

    /**
     * @test
     */
    public function delete_basic()
    {
        $this->assertTrue(mkdir($this->_topLevelDirPath));
        file_put_contents($this->_topLevelFilePath, 'some text');
        F::delete($this->_topLevelFilePath);
        $this->assertFalse(file_exists($this->_topLevelFilePath));
    }

    /**
     * @test
     */
    public function delete_nonExistent()
    {
        $this->assertFalse(file_exists('/path/does/not/exist'));
        F::delete('/path/does/not/exist');
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function delete_directory()
    {
        $this->assertTrue(mkdir($this->_topLevelDirPath));
        F::delete($this->_topLevelDirPath);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $path is not a string or is whitespace
     */
    public function delete_nonStringPath()
    {
        F::delete(1);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage $path is not a string or is whitespace
     */
    public function delete_pathIsWhitespace()
    {
        F::delete('  ');
    }
}
