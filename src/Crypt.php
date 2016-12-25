<?php

namespace sndsgd;

/**
 * An encryption helper
 */
class Crypt
{
    /**
     * An openssl cipher
     *
     * @var string
     */
    protected $cipher;

    /**
     * The digest method for hashing keys
     *
     * @var string
     */
    protected $digestMethod;

    /**
     * The initialization vector size
     *
     * @var int
     */
    protected $ivLength;

    /**
     * @param string $cipher The encryption cipher to use
     * @param string $digestMethod The digest method to use when hashing keys
     * @throws \InvalidArgumentException If either argument is invalid
     */
    public function __construct(
        string $cipher = "aes-256-ctr",
        string $digestMethod = "sha256"
    )
    {
        if (!in_array($cipher, openssl_get_cipher_methods(true))) {
            throw new \InvalidArgumentException(
                "invalid value provided for cipher"
            );
        }

        if (!in_array($digestMethod, openssl_get_md_methods(true))) {
            throw new \InvalidArgumentException(
                "invalid value provided for digestMethod"
            );
        }

        $this->cipher = $cipher;
        $this->digestMethod = $digestMethod;
        $this->ivLength = openssl_cipher_iv_length($cipher);
    }

    /**
     * Encrypt a value
     *
     * @param string $value The value to encrypt
     * @param string $key The key
     * @return string The encrypted value
     * @throws \Exception If the encryption fails
     */
    public function encryptBinary(string $value, string $key): string
    {
        $key = $this->hashKey($key);
        $opts = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING;
        $iv = random_bytes($this->ivLength);
        $encrypted = openssl_encrypt($value, $this->cipher, $key, $opts, $iv);
        if ($encrypted === false) {
            throw new \Exception("encryption failed; ".openssl_error_string());
        }
        return $iv.$encrypted;
    }

    /**
     * Decrypt a value
     *
     * @param string $value The encrypted value
     * @param string $key The encryption key
     * @return string The decrypted value
     * @throws \Exception If the decryption fails
     */
    public function decryptBinary(string $value, string $key): string
    {
        $iv = substr($value, 0, $this->ivLength);
        $encrypted = substr($value, $this->ivLength);
        $key = $this->hashKey($key);
        $opts = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING;
        $ret = openssl_decrypt($encrypted, $this->cipher, $key, $opts, $iv);
        if ($ret === false) {
            throw new \Exception("decryption failed; ".openssl_error_string());
        }
        return $ret;
    }

    /**
     * Serialize and encrypt a value
     *
     * @param mixed $value
     * @param string $key
     * @param boolean $base64 Whether to base64 encode the result
     * @return string|null
     */
    public function encrypt($value, string $key, bool $base64 = false): string
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
    public function decrypt(string $value, string $key, bool $base64 = false)
    {
        if ($base64) {
            $value = base64_decode($value);
        }
        $ret = $this->decryptBinary($value, $key);
        return unserialize($ret);
    }

    /**
     * Compute a digest hash for a value
     *
     * @param string $value The value to hash
     * @return string
     * @throws \Exception If `openssl_digest` fails
     */
    private function hashKey(string $value): string
    {
        $ret = openssl_digest($value, $this->digestMethod, true);
        if ($ret === false) {
            throw new \Exception("digest failed; ".openssl_error_string());
        }
        return $ret;
    }
}
