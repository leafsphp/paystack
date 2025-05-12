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
        $response = $this->client->get('/transaction');

        $transactions = json_decode($response->getBody(), true);

        return array_map(function ($transaction) {
            return new Transaction($transaction);
        }, $transactions['data']);
    }

    /**
     * Get a paystack transaction by id
     */
    public function get($id): Transaction
    {
        $response = $this->client->get("/transaction/$id");

        $transaction = json_decode($response->getBody(), true);

        return new Transaction($transaction['data']);
    }

    /**
     * Get a paystack transaction by reference
     */
    public function getByReference($reference): Transaction
    {
        $response = $this->client->get("/transaction/verify/$reference");

        $transaction = json_decode($response->getBody(), true);

        return new Transaction($transaction['data']);
    }

    /**
     * Update a paystack transaction
     */
    public function update($id, array $params): Transaction
    {
        $response = $this->client->put("/transaction/$id", [
            'body' => json_encode($params)
        ]);

        $transaction = json_decode($response->getBody(), true);

        return new Transaction($transaction['data']);
    }

    public function toArray()
    {
        return array_map(function ($transaction) {
            return $transaction->toArray();
        }, $this->all());
    }
}
