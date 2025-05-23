<?php

namespace Leaf\Billing\Paystack;

/**
 * PayStack Subscriptions
 * ----
 * API wrapper for PayStack Subscriptions API
 */
class Subscriptions
{
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Create a new paystack subscriptions
     */
    public function create(array $params): Subscription
    {
        $response = $this->client->post('/subscription', [
            'body' => json_encode($params)
        ]);

        $subscriptions = json_decode($response->getBody(), true);

        return new Subscription($subscriptions['data']);
    }

    /**
     * Get all paystack subscriptions
     */
    public function all(): array
    {
        $response = $this->client->get('/subscription');

        $subscriptions = json_decode($response->getBody(), true);

        return array_map(function ($subscription) {
            return new Subscription($subscription);
        }, $subscriptions['data']);
    }

    /**
     * Get a paystack subscription by id
     */
    public function get($id): Subscription
    {
        $response = $this->client->get("/subscription/$id");

        $subscription = json_decode($response->getBody(), true);

        return new Subscription($subscription['data']);
    }

    /**
     * Update a paystack subscription
     */
    public function update($id, array $params): Subscription
    {
        $response = $this->client->put("/subscription/$id", [
            'body' => json_encode($params)
        ]);

        $subscription = json_decode($response->getBody(), true);

        return new Subscription($subscription['data']);
    }

    /**
     * Cancel a paystack subscription
     */
    public function cancel($id): bool
    {
        $response = $this->client->post("/subscription/$id/disable");

        $subscription = json_decode($response->getBody(), true);

        return $subscription['status'];
    }

    public function toArray()
    {
        return array_map(function ($subscription) {
            return $subscription->toArray();
        }, $this->all());
    }
}
