<?php

namespace Leaf\Billing\PayStack;

/**
 * PayStack Transaction
 * ----
 * API wrapper for PayStack Transaction API
 */
class Transaction
{
    public $url;
    public $sccess_code;
    public $id;
    public $transaction_id;

    public $amount;
    public $currency;
    public $status;
    public $payment_status;
    public $metadata;
    public $subscription;

    public function __construct(array $transaction)
    {
        $this->url = $transaction['authorization_url'] ?? null;
        $this->sccess_code = $transaction['access_code'] ?? null;
        $this->id = $transaction['reference'];
        $this->transaction_id = $transaction['id'] ?? null;
        $this->amount = $transaction['amount'] ?? null;
        $this->currency = $transaction['currency'] ?? null;
        $this->status = $transaction['status'] ?? null;
        $this->metadata = $transaction['metadata'] ?? null;
        $this->subscription = $transaction['subscription'] ?? null;

        $this->status === 'success' ? $this->status = 'paid' : $this->status;
        $this->payment_status = $this->status;
    }

    public function toArray()
    {
        return [
            'authorization_url' => $this->url,
            'access_code' => $this->sccess_code,
            'reference' => $this->id,
        ];
    }
}
