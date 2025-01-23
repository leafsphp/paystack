<?php

namespace Leaf\Billing;

use Leaf\Billing;
use Leaf\Billing\PayStack\Provider;

require __DIR__ . '/Billing.php';

/**
 * Leaf Billing PayStack
 * -----------
 * PayStack provider for Leaf Billing
 */
class PayStack
{
    use Billing;

    /**
     * PayStack product
     */
    protected string $product;

    /**
     * PayStack plans/tiers
     */
    protected $tiers = [];

    protected function initProvider($billingSettings = [])
    {
        if ($billingSettings['provider']) {
            $config = [
                'api_key' => $billingSettings['secrets.apiKey'],
            ];

            if (isset($billingSettings['secrets.clientId'])) {
                $config['client_id'] = $billingSettings['secrets.clientId'];
            }

            $this->provider = new Provider($config);

            if (storage()->exists(StoragePath('billing/provider.json'))) {
                $provider = storage()->read(StoragePath('billing/provider.json'));
                $provider = json_decode($provider, true);

                $this->product = $provider['product'];
                $this->tiers = $provider['tiers'];
            } else {
                // paystack doesn't use products for tiered pricing so we'll hardcode it
                $this->product = 'Leaf Billing ' . _env('APP_NAME', '') . ' ' . time();

                $this->initTiers($billingSettings);

                storage()->createFile(StoragePath('billing/provider.json'), json_encode([
                    'product' => $this->product,
                    'tiers' => $this->tiers,
                ]), ['recursive' => true]);
            }
        }

        $this->config($billingSettings);
    }

    protected function initTiers($billingSettings)
    {
        foreach ($billingSettings['tiers'] as $tier) {
            $plan = [
                'name' => $tier['name'],
                'description' => $tier['description'],
                'currency' => $billingSettings['currency.name'],
                'send_invoices' => $tier['send_invoices'] ?? false,
                'send_sms' => $tier['send_sms'] ?? false,
                'metadata' => [
                    'tier' => $tier['name'],
                    'nickname' => $tier['name'],
                ],
            ];

            if ($tier['price'] ?? null) {
                // $payStackPlan = $this->provider->plans()->create(array_merge($plan, [
                //     'amount' => $tier['price'] * 100,
                // ]));

                // $this->tiers[$payStackPlan->id] = (new Tier($payStackPlan->id, $tier))->toArray();
            } else {
                if ($tier['price.daily'] ?? null) {
                    $payStackPlan = $this->provider->plans()->create(array_merge($plan, [
                        'amount' => $tier['price.daily'] * 100,
                        'interval' => 'daily',
                    ]));

                    $this->tiers[$payStackPlan->id] = (new Tier($payStackPlan->id, array_merge($tier, ['type' => 'daily'])))->toArray();
                }

                if ($tier['price.weekly'] ?? null) {
                    $payStackPlan = $this->provider->plans()->create(array_merge($plan, [
                        'amount' => $tier['price.weekly'] * 100,
                        'interval' => 'weekly',
                    ]));

                    $this->tiers[$payStackPlan->id] = (new Tier($payStackPlan->id, array_merge($tier, ['type' => 'weekly'])))->toArray();
                }

                if ($tier['price.monthly'] ?? null) {
                    $payStackPlan = $this->provider->plans()->create(array_merge($plan, [
                        'amount' => $tier['price.monthly'] * 100,
                        'interval' => 'month',
                    ]));

                    $this->tiers[$payStackPlan->id] = (new Tier($payStackPlan->id, array_merge($tier, ['type' => 'monthly'])))->toArray();
                }

                if ($tier['price.yearly'] ?? null) {
                    $payStackPlan = $this->provider->plans()->create(array_merge($plan, [
                        'amount' => $tier['price.yearly'] * 100,
                        'interval' => 'year',
                    ]));

                    $this->tiers[$payStackPlan->id] = (new Tier($payStackPlan->id, array_merge($tier, ['type' => 'yearly'])))->toArray();
                }
            }
        }
    }
}
