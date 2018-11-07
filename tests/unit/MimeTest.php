<?php

namespace sndsgd;

class MimeTest extends \PHPUnit\Framework\TestCase
{
    protected static $imageFile;
    protected static $binaryFile;

    public static function tearDownAfterClass()
    {
        foreach ([static::$imageFile, static::$binaryFile] as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    /**
     * @dataProvider providerGetTypeFromExtension
     */
    public function testGetTypeFromExtension($test, $expect)
    {
        $this->assertEquals($expect, Mime::getTypeFromExtension($test));
    }

    public function providerGetTypeFromExtension()
    {
        return [
            ["aiff", "audio/x-aiff"],
            ["atom", "application/atom+xml"],
            ["c", "text/x-csrc"],
            ["css", "text/css"],
            ["csv", "text/csv"],
            ["jpeg", "image/jpeg"],
            ["jpg", "image/jpeg"],
            ["js", "application/javascript"],
            ["json", "application/json"],
            ["mid", "audio/midi"],
            ["ogg", "audio/ogg"],
            ["txt", "text/plain"],
            ["zip", "application/zip"],
            ["asdasdasdasdasdasd", "application/octet-stream"],
        ];
    }

    /**
     * @dataProvider providerGetTypeFromFile
     */
    public function testGetTypeFromFile($path, $expect)
    {
        $result = Mime::getTypeFromFile($path);
        $this->assertEquals($expect, $result);
    }

    public function providerGetTypeFromFile()
    {
        static::$imageFile = tempnam(sys_get_temp_dir(), "mime-test-");
        $im = imagecreate(20, 20);
        imagecolorallocate($im, 0, 0, 0);
        imagepng($im, static::$imageFile);
        imagedestroy($im);

        static::$binaryFile = tempnam(sys_get_temp_dir(), "mime-test-");
        $crypt = new \sndsgd\Crypt();
        $binary = $crypt->encryptBinary(\sndsgd\Str::random(100), "pass");
        file_put_contents(static::$binaryFile, $binary);

        return [
            [__FILE__, "text/x-php"],
            [static::$imageFile, "image/png"],
            [static::$binaryFile, "application/octet-stream"],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetTypeFromFileException()
    {
        $path = __FILE__."/nothing/to/see/here.txt";
        Mime::getTypeFromFile($path);
    }

    public function testGetExtension()
    {
        $fn = "sndsgd\\Mime::getExtension";
        $tests = [
            "gif" => "image/gif",
            "jpg" => "image/jpeg",
            "js" => "application/javascript",
            "js" => "application/x-javascript",
            "css" => "text/css",
            "unknown" => ["doesnt/exist", "unknown"]
        ];
        foreach ($tests as $expect => $test) {
            $result = is_array($test)
                ? call_user_func_array($fn, $test)
                : call_user_func($fn, $test);
            $this->assertEquals($expect, $result);
        }
    }
}
