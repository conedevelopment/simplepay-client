<?php

namespace Cone\SimplePay;

class Client
{
    /**
     * Create a new SimplePay Client instance.
     */
    public function __construct(protected string $merchantId, protected string $secretKey)
    {
        // $this->config()->setApiKey('X-API-KEY', $apiKey);
    }

    /**
     * Get the configuration instance.
     */
    public function config(): Configuration
    {
        return Configuration::getDefaultConfiguration();
    }
}
