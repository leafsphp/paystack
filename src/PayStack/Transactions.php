<?php

namespace Leaf\Billing\PayStack;

/**
 * PayStack Transactions
 * ----
 * API wrapper for PayStack Transactions API
 */
class Transactions
{
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Create a new paystack transaction
     */
    public function create(array $params): Transaction
    {
        $response = $this->client->post('/transaction/initialize', [
            'body' => json_encode($params)
        ]);

        $transaction = json_decode($response->getBody(), true);

        return new Transaction($transaction['data']);
    }

    /**
     * Get all paystack transactions
     */
    public function all(): array
    {
        $response = $this->client->get('/customer');

        $transactions = json_decode($response->getBody(), true);

        return array_map(function ($customer) {
            return new Transaction($customer);
        }, $transactions['data']);
    }

    /**
     * Get a paystack customer by id
     */
    public function get($id): Transaction
    {
        $response = $this->client->get("/customer/$id");

        $transaction = json_decode($response->getBody(), true);

        return new Transaction($transaction['data']);
    }

    /**
     * Update a paystack transaction
     */
    public function update($id, array $params): Transaction
    {
        $response = $this->client->put("/customer/$id", [
            'body' => json_encode($params)
        ]);

        $transaction = json_decode($response->getBody(), true);

        return new Transaction($transaction['data']);
    }

    public function toArray()
    {
        return array_map(function ($customer) {
            return $customer->toArray();
        }, $this->all());
    }
}
