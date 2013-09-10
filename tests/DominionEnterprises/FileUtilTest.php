<?php
/**
 * Defines the FileUtilTest class
 */

namespace DominionEnterprises\Tests;
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

    //this is just for convience, DO NOT RELY ON IT
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
     */
    public function deleteDirectoryContents_nonStringPath()
    {
        F::deleteDirectoryContents(1);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionCode 1
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
     * @expectedExceptionCode 3
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
     * @expectedExceptionCode 2
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
    public function createTempFile_basicUse()
    {
        $dir = sys_get_temp_dir() . '/boo';

        if (is_dir($dir)) {
            foreach (glob("{$dir}/*") as $path) {
                unlink($path);
            }

            rmdir($dir);
        }

        $tempPath = F::createTempFile($dir, 'p');

        $this->assertTrue(file_exists($tempPath));
        $this->assertSame('', file_get_contents($tempPath));
    }

    /**
     * @test
     */
    public function createTempFile_timeout()
    {
        $start = T::inMillis();
        try {
            //trying somewhere not accessible like /bin
            F::createTempFile('/bin', 'p', 200);
            $this->fail('did not timeout');
        } catch (\Exception $e) {
            $end = T::inMillis();
            $this->assertTrue($end - $start >= 200);
            $this->assertTrue($end - $start < 400);
        }
    }

    /**
     * @test
     */
    public function createTempFile_badTempDir()
    {
        try {
            //trying somewhere not accessible like /bin
            F::createTempFile('/bin/boo', 'p');
            $this->fail('did not fail');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
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
}
