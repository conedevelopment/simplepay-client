# SimplePay PHP Client

This provides an OpenAPI schema and an auto-generated PHP client as a composer package.

> [!NOTE]
> This is not an official package.

## Requirements

- PHP 8.1+
- Curl, JSON, Multibyte String extensions

## Installation

```sh
composer require conedevelopment/simplepay-php
```

## Configuration

- SimplePay Merchant ID
- SimplePay Secret Key

## Usage

```php
$client = new Cone\SimplePay\Client('MERCHANT', 'SECRET_KEY');

$client->api()->start(...);
$client->api()->finish(...);
$client->api()->refund(...);
$client->api()->query(...);
$client->api()->do(...);
$client->api()->transactionCancel(...);
$client->api()->doRecurring(...);
$client->api()->cardQuery(...);
$client->api()->cardCancel(...);
$client->api()->tokenQuery(...);
$client->api()->tokenCancel(...);
$client->api()->startEam(...);
$client->api()->startApplePay(...);
$client->api()->doApplePay(...);
```

> [!NOTE]
> The client automatically adds the `merchant`, `salt` and `sdkVersion` parameters to the body as well as the `Signature` header to the request.

### Error Handling

SimplePay responses with `HTTP 200` even if the operation is failed or an error occured. The handle this properly the Client has a built-in middleware that checks whether the response contains any errors, if so, it throws an `ApiException`.

```php
try {
    $client->api()->start(...);
} catch (ApiException $exception) {
    Log::info()
}
```
