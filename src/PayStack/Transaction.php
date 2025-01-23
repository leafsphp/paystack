<?php

namespace Leaf\Billing\PayStack;

/**
 * PayStack Transaction
 * ----
 * API wrapper for PayStack Transaction API
 */
class Transaction
{
    public $authorization_url;
    public $access_code;
    public $reference;

    public function __construct(array $transaction)
    {
        $this->authorization_url = $transaction['authorization_url'];
        $this->access_code = $transaction['access_code'];
        $this->reference = $transaction['reference'];
    }

    public function toArray()
    {
        return [
            'authorization_url' => $this->authorization_url,
            'access_code' => $this->access_code,
            'reference' => $this->reference,
        ];
    }
}
