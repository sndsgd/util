<?php

namespace sndsgd;

trait ErrorTrait
{
    /**
     * An error message
     *
     * @var string|null
     */
    protected $error;

    /**
     * An detailed error message
     *
     * @var string|null
     */
    protected $errorDetail;

    /**
     * Set an error message
     *
     * @param string $msg
     */
    protected function setError(string $msg)
    {
        $this->error = $msg;
        $lastError = error_get_last();
        if ($lastError !== null) {
            $this->errorDetail = sprintf(
                "'%s' in %s on line %s",
                $lastError['message'],
                $lastError['file'],
                $lastError['line']
            );
        }
    }

    /**
     * Retreive the current error
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Retreive the current error detail
     *
     * @return string|null
     */
    public function getErrorDetail()
    {
        return $this->errorDetail;
    }

    /**
     * Reset the errors
     */
    public function clearError()
    {
        $this->error = $this->errorDetail = null;
    }
}
