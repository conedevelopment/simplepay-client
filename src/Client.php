<?php

namespace Cone\SimplePay;

use Closure;
use Cone\SimplePay\Api\TransactionApi;
use GuzzleHttp\Client as Http;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;

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
        protected string $merchant,
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

        $stack->push(function (callable $next): Closure {
            return function (RequestInterface $request, array $options) use ($next): PromiseInterface {
                $modify = [
                    'body' => [
                        'merchant' => $this->merchant,
                        'salt' => substr(str_shuffle(md5(microtime())), 0, 32),
                        'sdkVersion' => 'Cone OTP SimplePay PHP Client:' . static::VERSION,
                    ],
                ];

                return $next(Utils::modifyRequest($request, $modify), $options);
            };
        });

        $stack->push(function (callable $next): Closure {
            return function (RequestInterface $request, array $options) use ($next): PromiseInterface {
                $modify = [
                    'set_headers' => [
                        'Signature' => $this->sign($request->getBody()->getContents()),
                    ],
                ];

                return $next(Utils::modifyRequest($request, $modify), $options);
            };
        });

        return new Http([
            'handler' => $stack,
        ]);
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
