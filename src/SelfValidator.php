<?php declare(strict_types=1);

namespace sndsgd;

/**
 * An object that validates values in the constructor, registering errors as
 * they are encountered.
 */
abstract class SelfValidator
{
    /**
     * The key used for errors when the root value is not valid
     *
     * @var string
     */
    const ROOT_ERROR_KEY = "__ROOT__";

    /**
     * The key used to identify the value being validated
     *
     * @var string
     */
    protected $key;

    /**
     * A list of errors encountered while validating the value
     *
     * @var array<string,array<string>>
     */
    protected $errors = [];

    /**
     * Create a new instance of a self validating class
     *
     * @param string $key The key associated with the value to validate
     * @param mixed $values The value to validate
     */
    public function __construct(string $key, $values)
    {
        $this->key = $key;
        if ($this->verifyRequiredValues($values)) {
            $this->verifyValues($values);
        }
    }

    /**
     * Retrieve the names of keys that must be declared in the values array
     *
     * @return array<string>
     */
    public function getRequiredKeys(): array
    {
        return [];
    }

    /**
     * Ensure the required values exist
     *
     * @param mixed $values The value to check for required values
     * @return bool Whether further validation can be attempted
     */
    protected function verifyRequiredValues($values): bool
    {
        if (!is_array($values)) {
            $this->addError("", "expecting an array of values");
            return false;
        }

        foreach ($this->getRequiredKeys() as $key) {
            if (!array_key_exists($key, $values)) {
                $this->addError($key, "this property is required");
            }
        }

        return true;
    }

    /**
     * Perform validation on the provided values
     *
     * @param mixed $values The value to verify
     * @return void
     */
    protected function verifyValues($values): void
    {
        # iterate over the values calling the appriopriate setter for each of them
        foreach ($values as $key => $value) {
            $method = \sndsgd\Str::toCamelCase("set_$key");
            if (!method_exists($this, $method)) {
                $this->addError($key, "unexpected property");
                continue;
            }

            $this->$method($key, $value);
        }
    }

    /**
     * Add an error message for a given subkey
     *
     * @param string $subkey The deeped porition of the key associated with the value
     * @param string $message The message to add
     * @param string ...$vars Variables to inject into the message using sprintf()
     * @return void
     */
    public function addError(string $subkey, string $message, string ...$vars): void
    {
        $key = empty($this->key) ? $subkey : sprintf("%s.%s", $this->key, $subkey);
        $key = empty($key) ? self::ROOT_ERROR_KEY : $key;
        $key = rtrim($key, ".");

        if (!isset($this->errors[$key])) {
            $this->errors[$key] = [];
        }

        # append the error message under the appropriate key
        $this->errors[$key][] = empty($vars) ? $message : sprintf($message, ...$vars);
    }

    /**
     * Add one or more errors to this object
     * Used to pass a child's errors up to the parent
     *
     * @param array $errors The errors to add to this instance
     * @return void
     */
    protected function addErrors(array $errors): void
    {
        foreach ($errors as $key => $messages) {
            foreach ($messages as $message) {
                $this->addError($key, $message);
            }
        }
    }

    /**
     * Retrieve an errors encountered while validating the provided values
     *
     * @return array
     */
    public function getErrors(): array
    {
        ksort($this->errors);
        return $this->errors;
    }

    /**
     * Determine whether the instance is valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }
}
