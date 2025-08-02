# SimplePay PHP Client

This provides an OpenAPI schema and an auto-generated PHP client as a composer package.

> [!NOTE]
> This is not an official package.

## Requirements

- PHP 8.1+
- Curl, JSON, Multibyte String extensions

## Installation

- Composer
- Manual

## Configuration

- SimplePay Merchant ID
- SimplePay Secret Key

## Usage

```php
$client = new Cone\SimplePay\Client('MERCHANT', 'SECRET_KEY');
```
