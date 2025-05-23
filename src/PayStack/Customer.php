<?php

namespace Leaf\Billing\Paystack;

/**
 * PayStack Customer
 * ----
 * API wrapper for PayStack Customer API
 */
class Customer
{
    public $email;
    public $integration;
    public $domain;
    public $customer_code;
    public $id;
    public $identified;
    public $identifications;
    public $createdAt;
    public $updatedAt;

    public function __construct(array $customer)
    {
        $this->email = $customer['email'];
        $this->integration = $customer['integration'];
        $this->domain = $customer['domain'];
        $this->customer_code = $customer['customer_code'];
        $this->id = $customer['id'];
        $this->identified = $customer['identified'];
        $this->identifications = $customer['identifications'];
        $this->createdAt = $customer['createdAt'];
        $this->updatedAt = $customer['updatedAt'];
    }

    public function toArray()
    {
        return [
            'email' => $this->email,
            'integration' => $this->integration,
            'domain' => $this->domain,
            'customer_code' => $this->customer_code,
            'id' => $this->id,
            'identified' => $this->identified,
            'identifications' => $this->identifications,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
