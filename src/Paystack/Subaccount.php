<?php

namespace Leaf\Billing\Paystack;

/**
 * PayStack Subaccount
 * ----
 * API wrapper for PayStack Subaccount API
 */
class Subaccount
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function toArray()
    {
        return $this->data;
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }
}
