<?php

namespace Leaf\Billing\PayStack;

use GuzzleHttp\Client;

/**
 * PHP PayStack Integration
 * ----
 * API wrapper for PayStack API
 */
class Provider
{
    /**
     * PayStack Config
     */
    protected $config;

    protected $client;

    public function __construct($config)
    {
        $this->config = $config;

        $this->client = new Client(
            [
                'base_uri' => $this->config['provider.url'] ?? 'https://api.paystack.co',
                'headers' => [
                    'Authorization' => "Bearer {$config['secrets.apiKey']}",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]
        );
    }

    /**
     * Return paystack plans instance
     */
    public function plans()
    {
        return new Plans($this->client);
    }

    /**
     * Return paystack subscriptions instance
     */
    public function subscriptions()
    {
        return new Subscriptions($this->client);
    }

    /**
     * Return paystack customers instance
     */
    public function customers()
    {
        return new Customers($this->client);
    }

    /**
     * Return paystack transactions instance
     */
    public function transactions()
    {
        return new Transactions($this->client);
    }
}
