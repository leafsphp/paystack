<?php

namespace Leaf\Billing\PayStack;

/**
 * PayStack Subscription
 * ----
 * API wrapper for PayStack Subscription API
 */
class Subscription
{
    public $customer;
    public $plan;
    public $integration;
    public $domain;
    public $start;
    public $status;
    public $quantity;
    public $amount;
    public $subscription_code;
    public $email_token;
    public $id;
    public $createdAt;
    public $updatedAt;

    public function __construct(array $customer)
    {
        $this->customer = $customer['customer'];
        $this->plan = $customer['plan'];
        $this->integration = $customer['integration'];
        $this->domain = $customer['domain'];
        $this->start = $customer['start'];
        $this->status = $customer['status'];
        $this->quantity = $customer['quantity'];
        $this->amount = $customer['amount'];
        $this->subscription_code = $customer['subscription_code'];
        $this->email_token = $customer['email_token'];
        $this->id = $customer['id'];
        $this->createdAt = $customer['createdAt'];
        $this->updatedAt = $customer['updatedAt'];
    }

    public function toArray()
    {
        return [
            'customer' => $this->customer,
            'plan' => $this->plan,
            'integration' => $this->integration,
            'domain' => $this->domain,
            'start' => $this->start,
            'status' => $this->status,
            'quantity' => $this->quantity,
            'amount' => $this->amount,
            'subscription_code' => $this->subscription_code,
            'email_token' => $this->email_token,
            'id' => $this->id,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
