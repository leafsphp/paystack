<?php

namespace Leaf\Billing;

use Leaf\Billing\PayStack\Provider;

/**
 * Leaf Billing PayStack
 * -----------
 * PayStack provider for Leaf Billing
 */
class PayStack implements BillingProvider
{
    /**
     * Config for billing
     */
    protected $config = [];

    /**
     * PayStack provider
     */
    protected Provider $provider;

    /**
     * PayStack product
     */
    protected string $product;

    /**
     * PayStack plans/tiers
     */
    protected $tiers = [];

    /**
     * Errors caught during operations
     */
    protected array $errors = [];

    public function __construct($billingSettings = [])
    {
        $this->config = $billingSettings;
        $this->provider = new Provider($billingSettings['connection'] ?? []);

        if (storage()->exists(StoragePath('billing/paystack.json'))) {
            $provider = storage()->read(StoragePath('billing/paystack.json'));
            $provider = json_decode($provider, true);

            $this->product = $provider['product'];
            $this->tiers = $provider['tiers'];
        }
    }

    public function initialize(): bool
    {
        if (storage()->exists(StoragePath('billing/paystack.json'))) {
            $provider = storage()->read(StoragePath('billing/paystack.json'));
            $provider = json_decode($provider, true);

            $this->product = $provider['product'];
            $this->tiers = $provider['tiers'];
        } else {
            // paystack doesn't use products for tiered pricing so we'll hardcode it
            $this->product = 'Leaf Billing ' . _env('APP_NAME', '') . ' ' . time();
        }

        try {
            $this->initTiers($this->config['tiers']);
        } catch (\Throwable $th) {
            return false;
        }

        return storage()->createFile(StoragePath('billing/paystack.json'), json_encode([
            'product' => $this->product,
            'tiers' => $this->tiers,
        ]), ['recursive' => true]);

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

                    $this->tiers[$payStackPlan->plan_code] = (new Tier($payStackPlan->plan_code, array_merge($tier, [
                        'type' => 'daily',
                        'currency' => $payStackPlan->currency,
                    ])))->toArray();
                }

                if ($tier['price.weekly'] ?? null) {
                    $payStackPlan = $this->provider->plans()->create(array_merge($plan, [
                        'name' => $plan['name'] . ' (Weekly)',
                        'amount' => $tier['price.weekly'] * 100,
                        'interval' => 'weekly',
                    ]));

                    $this->tiers[$payStackPlan->plan_code] = (new Tier($payStackPlan->plan_code, array_merge($tier, [
                        'type' => 'weekly',
                        'currency' => $payStackPlan->currency,
                    ])))->toArray();
                }

                if ($tier['price.monthly'] ?? null) {
                    $payStackPlan = $this->provider->plans()->create(array_merge($plan, [
                        'name' => $plan['name'] . ' (Monthly)',
                        'amount' => $tier['price.monthly'] * 100,
                        'interval' => 'monthly',
                    ]));

                    $this->tiers[$payStackPlan->plan_code] = (new Tier($payStackPlan->plan_code, array_merge($tier, [
                        'type' => 'monthly',
                        'currency' => $payStackPlan->currency,
                    ])))->toArray();
                }

                if ($tier['price.yearly'] ?? null) {
                    $payStackPlan = $this->provider->plans()->create(array_merge($plan, [
                        'name' => $plan['name'] . ' (Yearly)',
                        'amount' => $tier['price.yearly'] * 100,
                        'interval' => 'annually',
                    ]));

                    $this->tiers[$payStackPlan->plan_code] = (new Tier($payStackPlan->plan_code, array_merge($tier, [
                        'type' => 'yearly',
                        'currency' => $payStackPlan->currency,
                    ])))->toArray();
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function customer(): ?Customer
    {
        try {
            return new Customer(
                $this->provider->customers()->get(auth()->user()->billing_id)
            );
            // return $this->provider->customers->retrieve(auth()->user()->billing_id);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function updateCustomer(string $customerId): void
    {
        if (auth()->user()->billing_id === $customerId) {
            return;
        }

        db()
            ->update(\Leaf\Auth\Config::get('db.table'))
            ->params(['billing_id' => $customerId])
            ->where(\Leaf\Auth\Config::get('id.key'), auth()->user()->id)
            ->execute();
    }

    /**
     * @inheritDoc
     */
    public function createCustomer(?array $data = null): bool
    {
        if ($customer = $this->customer()) {
            return true;
        }

        if (!$data) {
            $data = auth()->user();
        }

        try {
            $customer = $this->provider->customers()->create([
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'metadata' => [
                    'user_id' => auth()->user()->id,
                ],
            ]);

            $this->updateCustomer($customer->id);

            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function charge(array $data): Session
    {
        if (!isset($data['items'])) {
            unset($data['metadata']['items']);
        }

        return new Session(
            $this->provider->transactions()->create(
                array_merge([
                    'email' => $data['customer'] ?? null,
                    'amount' => $data['amount'],
                    'metadata' => $data['metadata'] ?? [],
                    'callback_url' => $data['url'] ?? (request()->getUrl() . $this->config['urls']['success']) ?? null,
                ], $data['_paystack'] ?? [])
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function subscribe(array $data): Session
    {
        $trialEnd = null;
        $user = auth()->user();

        $tier = ($data['id'] ?? null) ? $this->tiers[$data['id']] : array_values(array_filter($this->tiers, function ($tier) use ($data) {
            return $tier['name'] === $data['name'];
        }))[0];

        if ($tier['trialDays'] ?? null) {
            // Checkout Sessions are active for 24 hours after their creation and within that time frame the customer
            // can complete the payment at any time. Stripe requires the trial end at least 48 hours in the future
            // so that there is still at least a one day trial if your customer pays at the end of the 24 hours.
            // We also add 10 seconds of extra time to account for any delay with an API request onto Stripe.
            $minimumTrialPeriod = tick()
                ->add(48, 'hours')
                ->add(10, 'seconds');

            $trialEnd = (tick()->add($tier['trialDays'], 'days')->isBefore($minimumTrialPeriod)
            ? $minimumTrialPeriod->toTimestamp()
            : tick()->add($tier['trialDays'] + 1, 'days'))->toTimestamp();
        }

        $stripeData = [
            'customer' => $user->email,
            'items' => [
                [
                    'price' => $tier['id'],
                    'quantity' => 1,
                ]
            ],
            'metadata' => [
                'tier' => $tier['name'],
                'tier_id' => $tier['id'],
                'user' => $user->id,
            ],
            'urls' => [
                'success' => $data['urls']['success'] ?? (request()->getUrl() . $this->config['urls']['success'] . '?session_id={CHECKOUT_SESSION_ID}'),
                'cancel' => $data['urls']['cancel'] ?? (request()->getUrl() . $this->config['urls']['cancel'] . '?session_id={CHECKOUT_SESSION_ID}'),
            ],
            '_stripe' => [
                'mode' => 'subscription',
                'subscription_data' => [
                    'trial_end' => $trialEnd,
                ],
            ],
        ];

        if ($data['metadata'] ?? null) {
            $stripeData['metadata'] = array_merge($stripeData['metadata'], $data['metadata']);
        }

        if (!$trialEnd) {
            unset($stripeData['_stripe']['subscription_data']['trial_end']);
        }

        $session = $this->charge($stripeData);
        $subscription = auth()->user()->subscriptions()->first();

        if (!$subscription) {
            $originalTimeStampsConfig = \Leaf\Auth\Config::get('timestamps');

            \Leaf\Auth\Config::set(['timestamps' => false]);

            $subscription = auth()->user()->subscriptions()->create([
                'name' => $tier['name'],
                'plan_id' => $tier['id'],
                'payment_session_id' => $session->id,
                'status' => Subscription::STATUS_INCOMPLETE,
                'start_date' => tick()->format('YYYY-MM-DD HH:mm:ss'),
                'end_date' => tick()->add(1, rtrim($tier['billingPeriod'], 'ly'))->format('YYYY-MM-DD HH:mm:ss'),
                'trial_ends_at' => $trialEnd ? tick()->add($tier['trialDays'] + 1, 'days')->format('YYYY-MM-DD HH:mm:ss') : null,
            ]);

            \Leaf\Auth\Config::set(['timestamps' => $originalTimeStampsConfig]);
        }

        return $session;
    }

    /**
     * @inheritDoc
     */
    public function changeSubcription(array $data): bool
    {
        $user = auth()->user();

        $tier = ($data['id'] ?? null) ? $this->tiers[$data['id']] : array_values(array_filter($this->tiers, function ($tier) use ($data) {
            return $tier['name'] === $data['name'];
        }))[0];

        $oldSubscription = $this->provider->subscriptions()->get($user->subscription()['subscription_id']);
        $stripeData = [
            'items' => [
                [
                    'id' => $oldSubscription->items->data[0]->id,
                    'price' => $tier['id'],
                ]
            ],
            'proration_behavior' => 'create_prorations',
        ];

        if ($data['metadata'] ?? null) {
            $stripeData['metadata'] = array_merge($stripeData['metadata'], $data['metadata']);
        }

        try {
            $this->provider->subscriptions()->update($oldSubscription->id, $stripeData);
        } catch (\Throwable $th) {
            $this->errors[] = $th->getMessage();
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function cancelSubscription(string $id): bool
    {
        $user = auth()->user();

        if (!$user->subscription()) {
            return true;
        }

        try {
            $this->provider->subscriptions()->cancel($id);
        } catch (\Throwable $th) {
            $this->errors[] = $th->getMessage();
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function session(string $id): ?Session
    {
        try {
            return new Session(
                $this->provider->transactions()->getByReference($id)
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function webhook(): Event
    {
        if ($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', @file_get_contents('php://input'), $this->config['connection']['secrets.webhook'] ?? null)) {
            return response()->exit('Invalid signature', 400);
        }

        $event = request()->body();

        return new Event([
            'type' => $event['event'],
            'data' => $event['data'],
            'id' => $event['id'] ?? null,
            'created' => $event['data']['created_at'] ?? null,
            'customer' => $event['data']['customer'] ?? null,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function callback(): ?Session
    {
        return $this->session(request()->get('txref') ?? request()->get('reference'));
    }

    /**
     * @inheritDoc
     */
    public function tiers(?string $billingPeriod = null): array
    {
        if (!$billingPeriod) {
            return $this->tiers;
        }

        return array_filter($this->tiers, function ($tier) use ($billingPeriod) {
            return $tier['billingPeriod'] === $billingPeriod;
        });
    }

    /**
     * @inheritDoc
     */
    public function tier(string $id): ?array
    {
        return $this->tiers[$id] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function periods(): array
    {
        $populatedTiers = [];

        foreach ($this->tiers as $tier) {
            $populatedTiers[] = $tier['billingPeriod'];
        }

        return array_unique($populatedTiers);
    }

    /**
     * @inheritDoc
     */
    public function providerName(): string
    {
        return 'paystack';
    }

    /**
     * @inheritDoc
     */
    public function provider(): Provider
    {
        return $this->provider;
    }

    /**
     * @inheritDoc
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
