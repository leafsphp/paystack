<?php

namespace Leaf\Billing\Paystack;

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

    /**
     * Return paystack subaccounts instance
     */
    public function subaccounts()
    {
        return new Subaccounts($this->client);
    }

    /**
     * Return paystack accepted banks
     */
    public function getAvailableBanks($options = [])
    {
        $queryOptions = http_build_query($options);
        $queryOptions = str_replace(['%5B', '%5D'], ['[', ']'], $queryOptions);

        $response = $this->client->get("/bank?$queryOptions");

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to fetch banks from PayStack');
        }

        $data = json_decode($response->getBody()->getContents(), true);

        if (isset($data['data'])) {
            return $data['data'];
        }

        return [];
    }

    /**
     * Return all accepted countries
     */
    public function getAvailableCountries($options = [])
    {
        $response = $this->client->get('/country');

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to fetch countries from PayStack');
        }

        $data = json_decode($response->getBody()->getContents(), true);

        if (isset($data['data'])) {
            return $data['data'];
        }

        return [];
    }

    /**
     * Return paystack accepted regions/states
     */
    public function getAvailableRegions($country)
    {
        $response = $this->client->get("/address_verification/states?country=$country");

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Failed to fetch states from PayStack');
        }

        $data = json_decode($response->getBody()->getContents(), true);

        if (isset($data['data'])) {
            return $data['data'];
        }

        return [];
    }
}
