<?php

namespace sndsgd;

/**
 * @requires extension openssl
 */
class CryptTest extends \PHPUnit\Framework\TestCase
{
    use \phpmock\phpunit\PHPMock;

    /**
     * @dataProvider provideConstructorException
     */
    public function testConstructorException($cipher, $keyHash, $exceptionMessage)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new Crypt($cipher, $keyHash);
    }

    public function provideConstructorException(): array
    {
        return [
            ["invalid", "sha256", "invalid value provided for cipher"],
            ["aes-256-ctr", "invalid", "invalid value provided for digestMethod"],
        ];
    }

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
        $key = "test key";
        return [
            ["home-hackintosh", Str::random(10)],
            [Str::random(1000), Str::random(100)],
            [file_get_contents(__FILE__), Str::random(1000)],
            ["test 😂 emoji", Str::random(10000)],
        ];
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
        $key = "test key";
        return [
            [42, $key],
            [4.2, $key],
            [[1, 2, 3], $key],
        ];
    }

    public function testEncryptFailure()
    {
        $encryptMock = $this->getFunctionMock(__NAMESPACE__, "openssl_encrypt");
        $encryptMock->expects($this->any())->willReturn(false);

        $crypt = new Crypt();
        $this->expectException(\Exception::class);
        $crypt->encrypt("test", "test");
    }

    public function testDecryptFailure()
    {
        $encryptMock = $this->getFunctionMock(__NAMESPACE__, "openssl_decrypt");
        $encryptMock->expects($this->any())->willReturn(false);

        $crypt = new Crypt();
        $this->expectException(\Exception::class);
        $crypt->decrypt("test", "test");
    }

    public function testKeyDigestFailure()
    {
        $encryptMock = $this->getFunctionMock(__NAMESPACE__, "openssl_digest");
        $encryptMock->expects($this->any())->willReturn(false);

        $crypt = new Crypt();
        $this->expectException(\Exception::class);
        $crypt->encrypt("test", "test");
    }
}
