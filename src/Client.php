<?php

namespace Cone\SimplePay;

use Cone\SimplePay\Api\TransactionApi;
use GuzzleHttp\Client as Http;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;

class Client
{
    /**
     * The client version.
     */
    public const VERSION = '1.0.0';

    /**
     * The transaction API instance.
     */
    protected ?TransactionApi $transactions = null;

    /**
     * Create a new SimplePay Client instance.
     */
    public function __construct(
        protected string $merchantId,
        protected string $secretKey,
        protected ?Environment $env = null
    ) {
        $this->config()->setHost(match ($env) {
            $env::SANDBOX => 'https://sandbox.simplepay.hu/payment/v2',
            $env::SECURE => 'https://secure.simplepay.hu/payment/v2',
            default => 'https://secure.simplepay.hu/payment/v2'
        });
    }

    /**
     * Sign the given data.
     */
    public function sign(string $data): string
    {
        return base64_encode(
            hash_hmac('sha384', $data, $this->secretKey, true)
        );
    }

    /**
     * Validate the signature.
     */
    public function validateSignature(string $hash, string $data): bool
    {
        return hash_equals($hash, $this->sign($data));
    }

    /**
     * Get the configuration instance.
     */
    public function config(): Configuration
    {
        return Configuration::getDefaultConfiguration();
    }

    /**
     * Create a new Guzzle Client instance.
     */
    public function client(): ClientInterface
    {
        $stack = HandlerStack::create();

        // Siganture Header middleware
        // Form data middleware

        $client = new Http([
            'handler' => $stack,
        ]);

        return $client;
    }

    /**
     * Get the transaction API.
     */
    public function transactions(): TransactionApi
    {
        if (is_null($this->transactions)) {
            $this->transactions = new TransactionApi(
                $this->client(),
                $this->config()
            );
        }

        return $this->transactions;
    }
}
