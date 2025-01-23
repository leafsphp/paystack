<?php

namespace Leaf\Billing\PayStack;

/**
 * PayStack Customers
 * ----
 * API wrapper for PayStack Customers API
 */
class Customers
{
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Create a new paystack customer
     */
    public function create(array $params): Customer
    {
        $response = $this->client->post('/customer', [
            'body' => json_encode($params)
        ]);

        $customer = json_decode($response->getBody(), true);

        return new Customer($customer['data']);
    }

    /**
     * Get all paystack customers
     */
    public function all(): array
    {
        $response = $this->client->get('/customer');

        $customers = json_decode($response->getBody(), true);

        return array_map(function ($customer) {
            return new Customer($customer);
        }, $customers['data']);
    }

    /**
     * Get a paystack customer by id
     */
    public function get($id): Customer
    {
        $response = $this->client->get("/customer/$id");

        $customer = json_decode($response->getBody(), true);

        return new Customer($customer['data']);
    }

    /**
     * Update a paystack customer
     */
    public function update($id, array $params): Customer
    {
        $response = $this->client->put("/customer/$id", [
            'body' => json_encode($params)
        ]);

        $customer = json_decode($response->getBody(), true);

        return new Customer($customer['data']);
    }

    public function toArray()
    {
        return array_map(function ($customer) {
            return $customer->toArray();
        }, $this->all());
    }
}
