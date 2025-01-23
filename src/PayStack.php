<?php

namespace Leaf\Billing;

use Leaf\Billing;
use Leaf\Billing\PayStack\Provider;

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
            $this->provider = new Provider($billingSettings);

            if (storage()->exists(StoragePath('billing/paystack.json'))) {
                $provider = storage()->read(StoragePath('billing/paystack.json'));
                $provider = json_decode($provider, true);

                $this->product = $provider['product'];
                $this->tiers = $provider['tiers'];
            } else {
                // paystack doesn't use products for tiered pricing so we'll hardcode it
                $this->product = 'Leaf Billing ' . _env('APP_NAME', '') . ' ' . time();

                $this->initTiers($billingSettings);

                storage()->createFile(StoragePath('billing/paystack.json'), json_encode([
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
                'currency' => strtoupper($billingSettings['currency.name']),
                'send_invoices' => $tier['send_invoices'] ?? false,
                'send_sms' => $tier['send_sms'] ?? false,
            ];

            if ($tier['price'] ?? null) {
                // $payStackPlan = $this->provider->plans()->create(array_merge($plan, [
                //     'amount' => $tier['price'] * 100,
                // ]));

                // $this->tiers[$payStackPlan->plan_code] = (new Tier($payStackPlan->plan_code, $tier))->toArray();
            } else {
                if ($tier['price.daily'] ?? null) {
                    $payStackPlan = $this->provider->plans()->create(array_merge($plan, [
                        'name' => $plan['name'] . ' (Daily)',
                        'amount' => $tier['price.daily'] * 100,
                        'interval' => 'daily',
                    ]));

                    $this->tiers[$payStackPlan->plan_code] = (new Tier($payStackPlan->plan_code, array_merge($tier, ['type' => 'daily'])))->toArray();
                }

                if ($tier['price.weekly'] ?? null) {
                    $payStackPlan = $this->provider->plans()->create(array_merge($plan, [
                        'name' => $plan['name'] . ' (Weekly)',
                        'amount' => $tier['price.weekly'] * 100,
                        'interval' => 'weekly',
                    ]));

                    $this->tiers[$payStackPlan->plan_code] = (new Tier($payStackPlan->plan_code, array_merge($tier, ['type' => 'weekly'])))->toArray();
                }

                if ($tier['price.monthly'] ?? null) {
                    $payStackPlan = $this->provider->plans()->create(array_merge($plan, [
                        'name' => $plan['name'] . ' (Monthly)',
                        'amount' => $tier['price.monthly'] * 100,
                        'interval' => 'monthly',
                    ]));

                    $this->tiers[$payStackPlan->plan_code] = (new Tier($payStackPlan->plan_code, array_merge($tier, ['type' => 'monthly'])))->toArray();
                }

                if ($tier['price.yearly'] ?? null) {
                    $payStackPlan = $this->provider->plans()->create(array_merge($plan, [
                        'name' => $plan['name'] . ' (Yearly)',
                        'amount' => $tier['price.yearly'] * 100,
                        'interval' => 'annually',
                    ]));

                    $this->tiers[$payStackPlan->plan_code] = (new Tier($payStackPlan->plan_code, array_merge($tier, ['type' => 'yearly'])))->toArray();
                }
            }
        }
    }

    /**
     * Open payment link for item
     */
    public function link($item, $user)
    {
        $transaction = $this->provider->transactions()->create([
            'plan' => $item,
            'email' => $user?->email,
            'amount' => $this->tiers[$item]['price'] * 100,
            'metadata' => [
                'name' => $user?->name,
                'user_id' => $user?->id,
            ],
        ]);

        return $transaction->authorization_url;
    }

    public function createSubscription()
    {
        // /**
        //  * @var \Leaf\Billing\PayStack\Customer
        //  */
        // $customer = $this->provider->customers()->create([
        //     'email' => $user->email,
        //     'metadata' => [
        //         'name' => $user->name,
        //         'user_id' => $user->id,
        //     ],
        // ]);

        // $subscription = $this->provider->subscriptions()->create([
        //     'customer' => $customer->id,
        //     'plan' => $item,
        // ]);
    }
}
