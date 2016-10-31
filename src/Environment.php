<?php

namespace sndsgd;

/**
 * A class for retrieving information about the system environment
 */
class Environment extends \sndsgd\ArrayAbstract
{
    /**
     * The key in `$_SERVER` that contains the environment value
     *
     * @var string
     */
    const SYSTEM_ENVVAR_NAME = "APP_ENV";

    /**
     * The value for development environments
     *
     * @var string
     */
    const DEV = "dev";

    /**
     * The value for staging environments
     * Note: staging environments are treated as production environments; the
     * staging value exists so staging nodes can be differentiated from
     * production nodes using the system wide environment variable
     *
     * @var string
     */
    const STAGE = "stage";

    /**
     * The value for production environments
     *
     * @var string
     */
    const PROD = "prod";

    /**
     * A list of the valid node types
     *
     * @var array<string>
     */
    protected static $validNodeTypes = [
        self::DEV,
        self::STAGE,
        self::PROD,
    ];

    /**
     * A copy of `$_SERVER`
     *
     * @var array<string,mixed>
     */
    protected $values;

    /**
     * One of the following constants self::DEV, self::STAGE, or self::PROD
     *
     * @var string
     */
    protected $nodeType;

    /**
     * Similar to nodeType, but contains a value indicating the node type that
     * should be emulated
     *
     * @var string
     */
    protected $emulatedNodeType = "";

    /**
     * @param array<string,mixed> $values
     * @param string $emulateNodeType The environment to emulate
     * @throws \LogicException If the actual and emulated node types match
     */
    public function __construct(
        array $values = [],
        string $emulateNodeType = ""
    )
    {
        # allow array access, but force read only
        parent::__construct($values, true);

        # set the node type using the system environment variable
        # if the envvar is missing, just default to a dev environment
        $nodeType = $values[self::SYSTEM_ENVVAR_NAME] ?? self::DEV;
        $this->nodeType = $this->validateNodeType($nodeType);

        # if the node type should be emulated, we'll set it as another property
        # this gives us the ability to load the real node type's config (with
        # service connection details), but to execute conditional blocks for the
        # emulated node type
        if ($emulateNodeType !== "") {
            if ($emulateNodeType === $this->nodeType) {
                throw new \LogicException(
                    "invalid value provided for 'emulateNodeType'; ".
                    "emulating the actual node type is not permitted"
                );
            }
            $this->emulatedNodeType = $this->validateNodeType($emulateNodeType);
        }
    }

    /**
     * Verify a node type is valid
     *
     * @param string $nodeType The value to verify
     * @return string The verified node type
     * @throws \InvalidArgumentException If the node type is not valid
     */
    protected function validateNodeType(string $nodeType): string
    {
        if (!in_array($nodeType, static::$validNodeTypes)) {
            throw new \InvalidArgumentException(
                "invalid value node type '$nodeType'; expecting ".
                \sndsgd\Arr::implode(", ", static::$validNodeTypes, "or ")
            );
        }
        return $nodeType;
    }

    /**
     * Determine whether the current environment is emulated
     *
     * @return bool
     */
    public function isEmulated(): bool
    {
        return ($this->emulatedNodeType !== "");
    }

    /**
     * Determine whether the current environment is production
     *
     * @return bool
     */
    public function isProd(): bool
    {
        $nodeType = $this->isEmulated()
            ? $this->emulatedNodeType
            : $this->nodeType;

        return ($nodeType === self::PROD || $nodeType === self::STAGE);
    }

    /**
     * Determine whether the current environment is development
     *
     * @return bool
     */
    public function isDev(): bool
    {
        return !$this->isProd();
    }

    /**
     * Retrieve the node type
     *
     * @return string
     */
    public function getNodeType(): string
    {
        return $this->nodeType;
    }

    /**
     * Retrieve the emulated node type
     *
     * @return string
     */
    public function getEmulatedNodeType(): string
    {
        return $this->emulatedNodeType;
    }

    /**
     * Retrieve a single value, or a default value if it doesn't exist
     *
     * @param string $key The key of the value to return
     * @param mixed $default The value to return if $key does not exist
     * @return mixed
     */
    public function get(string $key, $default = "")
    {
        return $this->values[$key] ?? $default;
    }

    /**
     * Get a copy of the environment values
     *
     * @return array<string,mixed>
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
