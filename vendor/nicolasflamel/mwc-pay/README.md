# MWC Pay PHP SDK

### Description
PHP SDK for [MWC Pay](https://github.com/NicolasFlamel1/MWC-Pay).

### Installing
Run the following command from the root of your project to install this library and configure your project to use it.
```
composer require nicolasflamel/mwc-pay
```

### Usage
After an `MwcPay` object has been created, it can be used to create payments, query the status of payments, get the current price of MimbleWimble coin, and get info about MWC Pay's public server.

A high level overview of a payment's life cycle when using this SDK consists of the following steps:
1. The merchant creates a payment and gets the payment's URL from the response.
2. The buyer sends MimbleWimble Coin to that URL.
3. The merchant can optionally monitor the payment's status via the `getPaymentInfo` method, the `createPayment` method's `receivedCallback` parameter, the `createPayment` method's `confirmedCallback` parameter, and/or the `createPayment` method's `expiredCallback` parameter.
4. The payment's completed callback is ran once the payment achieves the desired number of on-chain confirmations.

The following code briefly shows how to use this SDK. A more complete example with error checking is available [here](https://github.com/NicolasFlamel1/MWC-Pay-PHP-SDK/tree/master/example).
```
<?php

// Require dependencies
require_once __DIR__ . "/vendor/autoload.php";

// Use MWC Pay
use Nicolasflamel\MwcPay\MwcPay;

// Initialize MWC Pay
$mwcPay = new MwcPay("http://localhost:9010");

// Create payment
$payment = $mwcPay->createPayment("123.456", 5, 600, "http://example.com/completed", "http://example.com/received", "http://example.com/confirmed", "http://example.com/expired", "notes");

// Get payment info
$paymentInfo = $mwcPay->getPaymentInfo($payment["payment_id"]);

// Get price
$price = $mwcPay->getPrice();

// Get public server info
$publicServerInfo = $mwcPay->getPublicServerInfo();

?>
```

### Functions
1. MWC Pay constructor: `constructor(string $privateServer = "http://localhost:9010"): MwcPay`

   This constructor is used to create an `MwcPay` object and it accepts the following parameter:
   * `string $privateServer` (optional): The URL for your MWC Pay's private server. If not provided then the default value `http://localhost:9010` will be used.

   This method returns the following value:
   * `MwcPay`: An `MwcPay` object.

2. MWC Pay create payment method: `createPayment(?string $price, ?int $requiredConfirmations, ?int $timeout, string $completedCallback, ?string $receivedCallback = NULL, ?string $confirmedCallback = NULL, ?string $expiredCallback = NULL, ?string $notes = NULL, ?string $apiKey = NULL): array | FALSE | NULL`

   This method is used to create a payment and it accepts the following parameters:
   * `?string $price`: The expected amount for the payment. If `NULL` then any amount will fulfill the payment.
   * `?int $requiredConfirmations`: The required number of on-chain confirmations that the payment must have before it's considered complete. If `NULL` then one required confirmation will be used.
   * `?int $timeout`: The duration in seconds that the payment can be received. If `NULL` then the payment will never expire.
   * `string $completedCallback`: The HTTP GET request that will be performed when the payment is complete. If the response status code to this request isn't `HTTP 200 OK`, then the same request will be made at a later time. This request can't follow redirects. This request may happen multiple times despite a previous attempt receiving an `HTTP 200 OK` response status code, so make sure to prepare for this and to respond to all requests with an `HTTP 200 OK` response status code if the request has already happened. All instances of `__id__`, `__completed__`, and `__received__` are replaced with the payment's ID, completed timestamp, and received timestamp respectively.
   * `?string $receivedCallback` (optional): The HTTP GET request that will be performed when the payment is received. If the response status code to this request isn't `HTTP 200 OK`, then an `HTTP 500 Internal Error` response will be sent to the payment's sender when they are sending the payment. This request can't follow redirects. This request may happen multiple times despite a previous attempt receiving an `HTTP 200 OK` response status code, so make sure to prepare for this and to respond to all requests with an `HTTP 200 OK` response status code if the request has already happened. All instances of `__id__`, `__price__`, `__sender_payment_proof_address__`, `__kernel_commitment__`, and `__recipient_payment_proof_signature__` are replaced with the payment's ID, price, sender payment proof address, kernel commitment, and recipient payment proof signature respectively. If not provided or `NULL` then no request will be performed when the payment is received.
   * `?string $confirmedCallback` (optional): The HTTP GET request that will be performed when the payment's number of on-chain confirmations changes and the payment isn't completed. The response status code to this request doesn't matter. This request can't follow redirects. All instances of `__id__`, and `__confirmations__` are replaced with the payment's ID and number of on-chain confirmations respectively. If not provided or `NULL` then no request will be performed when the payment's number of on-chain confirmations changes.
   * `?string $expiredCallback` (optional): The HTTP GET request that will be performed when the payment is expired. If the response status code to this request isn't `HTTP 200 OK`, then the same request will be made at a later time. This request can't follow redirects. This request may happen multiple times despite a previous attempt receiving an `HTTP 200 OK` response status code, so make sure to prepare for this and to respond to all requests with an `HTTP 200 OK` response status code if the request has already happened. All instances of `__id__` are replaced with the payment's ID. If not provided or `NULL` then no request will be performed when the payment is expired.
   * `?string $notes` (optional): Text to associate with the payment.
   * `?string $apiKey` (optional): API key that must match the private server's API key if it's using one.

   This method returns the following values:
   * `array`: The payment was created successfully. This array contains the `string payment_id`, `string url`, and `string recipient_payment_proof_address` of the created payment.
   * `FALSE`: An error occurred on the private server and/or communicating with the private server failed.
   * `NULL`: Parameters are invalid.

3. MWC Pay get payment info method: `getPaymentInfo(string $paymentId, ?string $apiKey = NULL): array | FALSE | NULL`

   This method is used to get the status of a payment and it accepts the following parameters:
   * `string $paymentId`: The payment's ID.
   * `?string $apiKey` (optional): API key that must match the private server's API key if it's using one.

   This method returns the following values:
   * `array`: This array contains the payment's `string url`, `?string price`, `int required_confirmations`, `bool received`, `int confirmations`, `?int time_remaining`, `string status`, and `string recipient_payment_proof_address`. The `string status` can be one of the following values: `Expired`, `Not received`, `Received`, `Confirmed`, or `Completed`.
   * `FALSE`: An error occurred on the private server and/or communicating with the private server failed.
   * `NULL`: Parameters are invalid and/or the payment doesn't exist.

4. MWC Pay get price method: `getPrice(?string $apiKey = NULL): string | FALSE | NULL`

   This method is used to get the price of MimbleWimble coin and it accepts the following parameters:
   * `?string $apiKey` (optional): API key that must match the private server's API key if it's using one.

   This method returns the following values:
   * `string`: The price of MimbleWimble Coin in USDT.
   * `FALSE`: An error occurred on the private server and/or communicating with the private server failed.
   * `NULL`: Parameters are invalid and/or the price API is disabled on the private server.

5. MWC Pay get public server info method: `getPublicServerInfo(?string $apiKey = NULL): array | FALSE | NULL`

   This method is used to get info about MWC Pay's public server and it accepts the following parameters:
   * `?string $apiKey` (optional): API key that must match the private server's API key if it's using one.

   This method returns the following values:
   * `array`: This array contains the public server's `string url` and `?string onion_service_address`.
   * `FALSE`: An error occurred on the private server and/or communicating with the private server failed.
   * `NULL`: Parameters are invalid.
