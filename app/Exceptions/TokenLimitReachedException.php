<?php

namespace App\Exceptions;

use Exception;
use App\Models\ClientToken;

class TokenLimitReachedException extends Exception
{
    protected ClientToken $clientToken;

    public function __construct(ClientToken $clientToken)
    {
        parent::__construct('Token limit reached');
        $this->clientToken = $clientToken;
    }

    public function getClientToken(): ClientToken
    {
        return $this->clientToken;
    }
}
