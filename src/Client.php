<?php

namespace Cone\SimplePay;

use Closure;
use Cone\SimplePay\Api\TransactionApi;
use GuzzleHttp\Client as Http;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * The client version.
     */
    public const VERSION = '1.0.0';

    /**
     * The transaction API instance.
     */
    protected ?TransactionApi $api = null;

    /**
     * Create a new SimplePay Client instance.
     */
    public function __construct(
        protected string $merchant,
        protected string $secretKey,
        protected ?Environment $env = null
    ) {
        $this->config()->setHost(match ($env) {
            Environment::SANDBOX => 'https://sandbox.simplepay.hu/payment/v2',
            Environment::SECURE => 'https://secure.simplepay.hu/payment/v2',
            default => 'https://secure.simplepay.hu/payment/v2'
        });
    }

    /**
     * Sign the given data.
     */
    protected function sign(string $data): string
    {
        return base64_encode(
            hash_hmac('sha384', $data, $this->secretKey, true)
        );
    }

    /**
     * Get the configuration instance.
     */
    protected function config(): Configuration
    {
        return Configuration::getDefaultConfiguration();
    }

    /**
     * Create a new Guzzle Client instance.
     */
    protected function client(): ClientInterface
    {
        $stack = HandlerStack::create();

        $stack->before('prepare_body', function (callable $next): Closure {
            return function (RequestInterface $request, array $options) use ($next): PromiseInterface {
                $data = json_decode((string) $request->getBody(), true) ?? [];

                $data = array_merge($data, [
                    'merchant' => $this->merchant,
                    'salt' => substr(str_shuffle(md5(microtime())), 0, 32),
                    'sdkVersion' => 'Cone OTP SimplePay PHP Client:' . static::VERSION,
                ]);

                $modify = [
                    'body' => Utils::streamFor($data = json_encode($data)),
                    'set_headers' => [
                        'Signature' => $this->sign($data),
                    ],
                ];

                return $next(Utils::modifyRequest($request, $modify), $options);
            };
        });

        $stack->push(Middleware::mapResponse(function (ResponseInterface $response): ResponseInterface {
            $body = (string) $response->getBody();

            if (! hash_equals($response->getHeader('Signature')[0] ?? '', $this->sign($body))) {
                throw new ApiException('Invalid Signature.', 999, $response->getHeaders(), $response->getBody());
            }

            $body = json_decode($body, true) ?? [];

            if (empty($body['redirectUrl'] ?? '') && ! empty($body['errorCodes'] ?? [])) {
                throw new ApiException(
                    'SimplePay error.',
                    (int) $body['errorCodes'][0] ?? 999,
                    $response->getHeaders(),
                    $response->getBody()
                );
            }

            return $response;
        }));

        return new Http([
            'handler' => $stack,
        ]);
    }

    /**
     * Get the transaction API.
     */
    public function api(): TransactionApi
    {
        return $this->api ??= new TransactionApi(
            $this->client(),
            $this->config()
        );
    }
}
