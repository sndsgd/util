<?php

namespace sndsgd;

/**
 * @requires extension mcrypt
 */
class CryptTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerBinary
     */
    public function testBinary($plaintext, $key)
    {
        $crypt = new Crypt();
        $encrypted = $crypt->encryptBinary($plaintext, $key);
        $decrypted = $crypt->decryptBinary($encrypted, $key);
        $this->assertSame($plaintext, $decrypted);
    }

    public function providerBinary()
    {
        $key = 'test key';
        return [
            ["home-hackintosh", $key],
            [Str::random(100000), $key],
            ["test 😂 emoji", $key],
        ];
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Warning
     */
    public function testEncryptFailure()
    {
        $crypt = new Crypt();

        # tweak the cipher so the `mcrypt_encrypt` call fails
        $rc = new \ReflectionClass($crypt);
        $cipher = $rc->getProperty('cipher');
        $cipher->setAccessible(true);
        $cipher->setValue($crypt, 'asd');

        $crypt->encryptBinary('test', 'test');
    }

    /**
     * @expectedException \Exception
     */
    public function testEncryptException()
    {
        $stub = $this->getMockBuilder("sndsgd\\Crypt")
            ->setMethods(['mencrypt'])
            ->getMock();

        $stub->method('mencrypt')->willReturn(false);
        $stub->encryptBinary('test', 'test');
    }

    /**
     * @expectedException \PHPUnit_Framework_Error_Warning
     */
    public function testDecryptFailure()
    {
        $crypt = new Crypt();

        # tweak the cipher so the `mcrypt_encrypt` call fails
        $rc = new \ReflectionClass($crypt);
        $cipher = $rc->getProperty('cipher');
        $cipher->setAccessible(true);
        $cipher->setValue($crypt, 'asd');

        $crypt->decryptBinary('test', 'test');
    }

    /**
     * @expectedException \Exception
     */
    public function testDecryptException()
    {
        $stub = $this->getMockBuilder("sndsgd\\Crypt")
            ->setMethods(['mdecrypt'])
            ->getMock();

        $stub->method('mdecrypt')->willReturn(false);
        $stub->decryptBinary('test', 'test');
    }

    /**
     * @dataProvider providerEncryptDecrypt
     */
    public function testEncryptDecrypt($input, $key)
    {
        $crypt = new Crypt();
        $encrypted = $crypt->encrypt($input, $key);
        $decrypted = $crypt->decrypt($encrypted, $key);
        $this->assertSame($input, $decrypted);

        # base64=true
        $encrypted = $crypt->encrypt($input, $key, true);
        $decrypted = $crypt->decrypt($encrypted, $key, true);
        $this->assertSame($input, $decrypted);
    }

    public function providerEncryptDecrypt()
    {
        $key = 'test key';
        return [
            [42, $key],
            [4.2, $key],
            [[1, 2, 3], $key],
        ];
    }
}
