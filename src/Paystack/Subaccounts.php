<?php

namespace Leaf\Billing\Paystack;

/**
 * PayStack Subaccounts
 * ----
 * API wrapper for PayStack Subaccounts API
 */
class Subaccounts
{
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Create a new paystack subaccount
     */
    public function create(array $params): Subaccount
    {
        $response = $this->client->post('/subaccount', [
            'body' => json_encode($params)
        ]);

        $subaccount = json_decode($response->getBody(), true);

        return new Subaccount($subaccount['data']);
    }

    /**
     * Get all paystack subaccounts
     */
    public function all(): array
    {
        $response = $this->client->get('/subaccount');

        $subaccounts = json_decode($response->getBody(), true);

        return array_map(function ($subaccount) {
            return new Subaccount($subaccount);
        }, $subaccounts['data']);
    }

    /**
     * Get a paystack subaccount by id
     */
    public function get($id): Subaccount
    {
        $response = $this->client->get("/subaccount/$id");

        $subaccount = json_decode($response->getBody(), true);

        return new Subaccount($subaccount['data']);
    }

    /**
     * Update a paystack subaccount
     */
    public function update($id, array $params): Subaccount
    {
        $response = $this->client->put("/subaccount/$id", [
            'body' => json_encode($params)
        ]);

        $subaccount = json_decode($response->getBody(), true);

        return new Subaccount($subaccount['data']);
    }

    public function toArray()
    {
        return array_map(function ($subaccount) {
            return $subaccount->toArray();
        }, $this->all());
    }
}
