<?php

namespace Leaf\Billing\PayStack;

/**
 * PayStack Plans
 * ----
 * API wrapper for PayStack Plans API
 */
class Plans
{
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Create a new paystack plan
     */
    public function create(array $params): Plan
    {
        $response = $this->client->post('/plan', [
            'body' => json_encode($params)
        ]);

        $plan = json_decode($response->getBody(), true);

        return new Plan($plan['data']);
    }

    /**
     * Get all paystack plans
     */
    public function all(): array
    {
        $response = $this->client->get('/plan');

        $plans = json_decode($response->getBody(), true);

        return array_map(function ($plan) {
            return new Plan($plan);
        }, $plans['data']);
    }

    /**
     * Get a paystack plan by id
     */
    public function get($id): Plan
    {
        $response = $this->client->get("/plan/$id");

        $plan = json_decode($response->getBody(), true);

        return new Plan($plan['data']);
    }

    public function toArray()
    {
        return array_map(function ($plan) {
            return $plan->toArray();
        }, $this->all());
    }
}
