<?php

namespace App\Exceptions;

class McpAuthenticationException extends McpServerException
{
    private bool $mfaRequired = false;

    public function setMfaRequired(bool $required = true): self
    {
        $this->mfaRequired = $required;

        return $this;
    }

    public function isMfaRequired(): bool
    {
        return $this->mfaRequired;
    }
}
