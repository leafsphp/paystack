<?php

namespace Leaf\Billing\PayStack;

/**
 * PayStack Plan
 * ----
 * API wrapper for PayStack Plan API
 */
class Plan
{
    public $name;
    public $amount;
    public $interval;
    public $integration;
    public $domain;
    public $plan_code;
    public $send_invoices;
    public $send_sms;
    public $hosted_page;
    public $currency;
    public $id;
    public $createdAt;
    public $updatedAt;

    public function __construct(array $plan)
    {
        $this->name = $plan['name'];
        $this->amount = $plan['amount'];
        $this->interval = $plan['interval'];
        $this->integration = $plan['integration'];
        $this->domain = $plan['domain'];
        $this->plan_code = $plan['plan_code'];
        $this->send_invoices = $plan['send_invoices'];
        $this->send_sms = $plan['send_sms'];
        $this->hosted_page = $plan['hosted_page'];
        $this->currency = $plan['currency'];
        $this->id = $plan['id'];
        $this->createdAt = $plan['createdAt'];
        $this->updatedAt = $plan['updatedAt'];
    }

    /**
     * Save current plan to paystack
     */
    public function save()
    {
        // Save plan to paystack
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'amount' => $this->amount,
            'interval' => $this->interval,
            'integration' => $this->integration,
            'domain' => $this->domain,
            'plan_code' => $this->plan_code,
            'send_invoices' => $this->send_invoices,
            'send_sms' => $this->send_sms,
            'hosted_page' => $this->hosted_page,
            'currency' => $this->currency,
            'id' => $this->id,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
