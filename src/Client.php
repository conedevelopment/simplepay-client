<?php

namespace Cone\SimplePay;

class Client
{
    /**
     * The client version.
     */
    public const VERSION = '1.0.0';

    /**
     * Create a new SimplePay Client instance.
     */
    public function __construct(
        protected string $merchantId,
        protected string $secretKey,
        protected bool $sandbox = false
    ) {
        // $this->config()->setApiKey('X-API-KEY', $apiKey);
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
}
