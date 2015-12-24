<?php

namespace sndsgd;

class Crypt
{
    /**
     * An mcrypt cipher
     *
     * @var string
     */
    protected $cipher;

    /**
     * An mcrypt mode
     *
     * @var string
     */
    protected $mode;

    /**
     * The initialization vector size
     *
     * @var int
     */
    protected $ivSize;

    /**
     * @param string $cipher
     * @param string $mode
     */
    public function __construct(
        $cipher = MCRYPT_RIJNDAEL_128, 
        $mode = MCRYPT_MODE_CBC
    ) {
        $this->cipher = $cipher;
        $this->mode = $mode;
        $this->ivSize = mcrypt_get_iv_size($cipher, $mode);
    }

    /**
     * Call `mcrypt_encrypt`
     * Stubbable for testing failures
     */
    protected function mencrypt($key, $value, $iv)
    {
        return mcrypt_encrypt($this->cipher, $key, $value, $this->mode, $iv);
    }

    /**
     * Call `mcrypt_decrypt`
     * Stubbable for testing failures
     */
    protected function mdecrypt($key, $value, $iv)
    {
        return mcrypt_decrypt($this->cipher, $key, $value, $this->mode, $iv);
    }

    /**
     * Encrypt a value
     *
     * @param string $value The value top encrypt
     * @param string $key The key 
     * @return string|null On success returns a string, otherwise null
     * @throws \Exception If the encryption fails
     */
    public function encryptBinary($value, $key)
    {
        $iv = mcrypt_create_iv($this->ivSize, MCRYPT_DEV_URANDOM);
        $ret = $this->mencrypt($this->packKey($key), $value, $iv);
        if ($ret === false) {
            throw new \Exception("decryption failure");
        }
        return $iv.$ret;
    }

    /**
     * Decrypt a value
     * Note: this will trim null bytes from the end of the decrypted result
     *
     * @param string $value The decrypted value
     * @param string $key The encryption key
     * @return string|null On success returns a string, otherwise null
     * @throws \Exception If the decryption fails
     */
    public function decryptBinary($value, $key)
    {
        $iv = substr($value, 0, $this->ivSize);
        $value = substr($value, $this->ivSize);
        $ret = $this->mdecrypt($this->packKey($key), $value, $iv);
        if ($ret === false) {
            throw new \Exception("encryption failure");
        }
        return rtrim($ret, "\0");
    }

    /**
     * Serialize and encrypt a value
     *
     * @param mixed $value
     * @param string $key
     * @param boolean $base64 Whether to base64 encode the result
     * @return string|null
     */
    public function encrypt($value, $key, $base64 = false)
    {
        $ret = $this->encryptBinary(serialize($value), $key);
        return ($base64) ? base64_encode($ret) : $ret;
    }

    /**
     * Decrypt a serialized value
     *
     * @param string $value
     * @param string $key
     * @param boolean $base64 Whether to base64 decode the value before decrypting
     * @return mixed
     */
    public function decrypt($value, $key, $base64 = false)
    {
        if ($base64) {
            $value = base64_decode($value);
        }
        $ret = $this->decryptBinary($value, $key);
        return unserialize($ret);
    }

    /**
     * Used to ensure a key is the appropriate length
     *
     * @param string $key
     * @param int $length The desired key length
     * @return string
     */
    private function packKey($key, $length = 32)
    {
        $hash = sha1($key);
        $hash .= strrev($hash);
        return pack("H*", substr($hash, 0, $length * 2));
    }
}
