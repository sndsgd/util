<?php

use \org\bovigo\vfs\vfsStream;
use \sndsgd\File;
use \sndsgd\Path;
use \sndsgd\Str;
use \sndsgd\Temp;


/**
 * @coversDefaultClass \sndsgd\Temp
 */
class TempTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->root = vfsStream::setup("root");
        $this->tmpdir = vfsStream::url("root");
        Temp::cleanup();
    }

    /**
     * @covers nothing
     */ 
    public function tearDown()
    {
        Temp::setDir(null);
    }

    /**
     * @covers ::setDir
     */ 
    public function testSetDir()
    {
        $class = new \ReflectionClass("sndsgd\\Temp");
        $property = $class->getProperty("dir");
        $property->setAccessible(true);

        Temp::setDir($this->tmpdir);
        $this->assertEquals($this->tmpdir, $property->getValue());

        Temp::setDir(null);
        $this->assertNull($property->getValue());
    }

    /**
     * @covers ::setDir
     * @expectedException InvalidArgumentException
     */
    public function testSetDirInvalidArgument()
    {
        Temp::setDir(42);
    }

    /**
     * @covers ::setDir
     * @expectedException InvalidArgumentException
     */
    public function testSetDirBadPath()
    {
        Temp::setDir("{$this->tmpdir}/does/not/exist");
    }

    /**
     * @covers ::getDir
     */
    public function testGetDir()
    {
        $this->assertEquals(sys_get_temp_dir(), Temp::getDir());

        Temp::setDir($this->tmpdir);
        $this->assertEquals($this->tmpdir, Temp::getDir());
    }

    /**
     * @covers nothing
     */
    private function createTempPath()
    {
        $path = "{$this->tmpdir}/".Str::random(32);
        touch($path);
        return $path;
    }

    /**
     * @covers ::registerPath
     */   
    public function testRegisterPath()
    {
        $path = $this->createTempPath();
        Temp::registerPath($path);
    }

    /**
     * @covers ::deregisterPath
     * @covers ::removePath
     */
    public function testDeregisterPath()
    {
        # deregister
        $path = $this->createTempPath();
        Temp::registerPath($path);
        $this->assertTrue(file_exists($path));
        $this->assertTrue(Temp::deregisterPath($path));
        $this->assertTrue(file_exists($path));
        $this->assertFalse(Temp::deregisterPath($path));

        # deregister and remove
        $path = $this->createTempPath();
        Temp::registerPath($path);
        $this->assertTrue(file_exists($path));
        $this->assertTrue(Temp::deregisterPath($path, true));
        $this->assertFalse(file_exists($path));
        $this->assertFalse(Temp::deregisterPath($path));
    }

    /**
     * @covers ::file
     */
    public function testFile()
    {
        $path = Temp::file("test");
        $this->assertTrue(file_exists($path));
        $this->assertEquals(0, filesize($path));

        $contents = "hello world";
        $path = Temp::file("test", $contents);
        $this->assertTrue(file_exists($path));
        $this->assertEquals($contents, file_get_contents($path));

        $path = Temp::file("test.txt");
        list($name, $ext) = File::splitName($path);
        $this->assertEquals("txt", $ext);
    }

    /**
     * @covers ::dir
     */
    public function testDir()
    {
        $path = Temp::dir("test-");
        $this->assertTrue(file_exists($path) && is_dir($path));
    }

    /**
     * @covers ::cleanup
     * @covers ::removePath
     */
    public function testCleanup()
    {
        $this->assertTrue(Temp::cleanup());

        # make a file that cannot be removed and register it for cleanup
        $dir = vfsStream::newDirectory("test")
            ->at($this->root)
            ->chmod(0700)
            ->chgrp(vfsStream::GROUP_ROOT)
            ->chown(vfsStream::OWNER_ROOT);
        $file = vfsStream::newFile("file.txt")
            ->setContent("content...")
            ->at($dir)
            ->chmod(0600)
            ->chgrp(vfsStream::GROUP_ROOT)
            ->chown(vfsStream::OWNER_ROOT);

        $path = vfsStream::url($file->path());
        Temp::registerPath($path);
        $this->assertFalse(Temp::cleanup());
    }
}