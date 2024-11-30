<?php


// Enforce strict types
declare(strict_types=1);

// Namespace
namespace Nicolasflamel\MwcPay;


// Classes

// MWC Pay class
final class MwcPay {

	// Constructor
	public function __construct(private string $privateServer = "http://localhost:9010") {
	
	}
	
	// Create payment
	public function createPayment(?string $price, ?int $requiredConfirmations, ?int $timeout, string $completedCallback, ?string $receivedCallback = NULL, ?string $confirmedCallback = NULL, ?string $expiredCallback = NULL, ?string $notes = NULL, ?string $apiKey = NULL): array | FALSE | NULL {
	
		// Check if sending creating payment request to the private server failed
		$createPaymentResponse = @file_get_contents($this->privateServer . "/create_payment?" . http_build_query(array_filter([
		
			// Price
			"price" => $price,
			
			// Required confirmations
			"required_confirmations" => $requiredConfirmations,
			
			// Timeout
			"timeout" => $timeout,
			
			// Completed callback
			"completed_callback" => $completedCallback,
			
			// Received callback
			"received_callback" => $receivedCallback,
			
			// Confirmed callback
			"confirmed_callback" => $confirmedCallback,
			
			// Expired callback
			"expired_callback" => $expiredCallback,
			
			// Notes
			"notes" => $notes,
			
			// API key
			"api_key" => $apiKey
			
		], function(string | int | NULL $value): bool {
		
			// Return if value isn't null
			return $value !== NULL;
		})));
		
		if($createPaymentResponse === FALSE) {
		
			// Check if an error occurred on the private server
			if(isset($http_response_header) === FALSE || is_array($http_response_header) === FALSE || count($http_response_header) <= 0 || preg_match('/HTTP\/[^ ]+ (\d+)/u', $http_response_header[0], $statusCode) !== 1 || $statusCode[1] === "500") {
			
				// Return false
				return FALSE;
			}
			
			// Otherwise assume request was invalid
			else {
			
				// Return null
				return NULL;
			}
		}
		
		// Try
		try {
		
			// Get payment info from create payment response
			$paymentInfo = json_decode($createPaymentResponse, TRUE, 2, JSON_THROW_ON_ERROR);
		}
		
		// Catch errors
		catch(\Throwable $error) {
		
			// Return false
			return FALSE;
		}
		
		// Check if payment info's payment ID, URL, or recipient payment proof address are invalid
		if(is_array($paymentInfo) === FALSE || array_key_exists("payment_id", $paymentInfo) === FALSE || is_string($paymentInfo["payment_id"]) === FALSE || $paymentInfo["payment_id"] === "" || array_key_exists("url", $paymentInfo) === FALSE || is_string($paymentInfo["url"]) === FALSE || $paymentInfo["url"] === "" || array_key_exists("recipient_payment_proof_address", $paymentInfo) === FALSE || is_string($paymentInfo["recipient_payment_proof_address"]) === FALSE || $paymentInfo["recipient_payment_proof_address"] === "") {
		
			// Return false
			return FALSE;
		}
		
		// Return payment info's payment ID, URL, and recipient payment proof address
		return [
		
			// Payment ID
			"payment_id" => $paymentInfo["payment_id"],
			
			// URL
			"url" => $paymentInfo["url"],
			
			// Recipient payment proof address
			"recipient_payment_proof_address" => $paymentInfo["recipient_payment_proof_address"]
		];
	}
	
	// Get payment info
	public function getPaymentInfo(string $paymentId, ?string $apiKey = NULL): array | FALSE | NULL {
	
		// Check if sending get payment info request to the private server failed
		$getPaymentInfoResponse = @file_get_contents($this->privateServer . "/get_payment_info?" . http_build_query(array_filter([
		
			// Payment ID
			"payment_id" => $paymentId,
			
			// API key
			"api_key" => $apiKey
			
		], function(string | int | NULL $value): bool {
		
			// Return if value isn't null
			return $value !== NULL;
		})));
		
		if($getPaymentInfoResponse === FALSE) {
		
			// Check if an error occurred on the private server
			if(isset($http_response_header) === FALSE || is_array($http_response_header) === FALSE || count($http_response_header) <= 0 || preg_match('/HTTP\/[^ ]+ (\d+)/u', $http_response_header[0], $statusCode) !== 1 || $statusCode[1] === "500") {
			
				// Return false
				return FALSE;
			}
			
			// Otherwise assume request was invalid
			else {
			
				// Return null
				return NULL;
			}
		}
		
		// Try
		try {
		
			// Get payment info from get payment info response
			$paymentInfo = json_decode($getPaymentInfoResponse, TRUE, 2, JSON_THROW_ON_ERROR);
		}
		
		// Catch errors
		catch(\Throwable $error) {
		
			// Return false
			return FALSE;
		}
		
		// Check if payment info's URL, price, required confirmations, received, confirmations, time remaining, status, or recipient payment proof address are invalid
		if(is_array($paymentInfo) === FALSE || array_key_exists("url", $paymentInfo) === FALSE || is_string($paymentInfo["url"]) === FALSE || $paymentInfo["url"] === "" || array_key_exists("price", $paymentInfo) === FALSE || ($paymentInfo["price"] !== NULL && is_string($paymentInfo["price"]) === FALSE) || ($paymentInfo["price"] !== NULL && preg_match('/^(?:0(?:\.\d+)?|[1-9]\d*(?:\.\d+)?)$/u', $paymentInfo["price"]) !== 1) || array_key_exists("required_confirmations", $paymentInfo) === FALSE || is_int($paymentInfo["required_confirmations"]) === FALSE || $paymentInfo["required_confirmations"] <= 0 || array_key_exists("received", $paymentInfo) === FALSE || is_bool($paymentInfo["received"]) === FALSE || array_key_exists("confirmations", $paymentInfo) === FALSE || is_int($paymentInfo["confirmations"]) === FALSE || $paymentInfo["confirmations"] < 0 || $paymentInfo["confirmations"] > $paymentInfo["required_confirmations"] || array_key_exists("time_remaining", $paymentInfo) === FALSE || ($paymentInfo["time_remaining"] !== NULL && is_int($paymentInfo["time_remaining"]) === FALSE) || ($paymentInfo["time_remaining"] !== NULL && $paymentInfo["time_remaining"] < 0) || array_key_exists("status", $paymentInfo) === FALSE || is_string($paymentInfo["status"]) === FALSE || array_key_exists("recipient_payment_proof_address", $paymentInfo) === FALSE || is_string($paymentInfo["recipient_payment_proof_address"]) === FALSE || $paymentInfo["recipient_payment_proof_address"] === "") {
		
			// Return false
			return FALSE;
		}
		
		// Return payment info's URL, price, required confirmations, received, confirmations, time remaining, status, and recipient payment proof address
		return [
		
			// URL
			"url" => $paymentInfo["url"],
			
			// Price
			"price" => $paymentInfo["price"],
			
			// Required confirmations
			"required_confirmations" => $paymentInfo["required_confirmations"],
			
			// Received
			"received" => $paymentInfo["received"],
			
			// Confirmations
			"confirmations" => $paymentInfo["confirmations"],
			
			// Time remaining
			"time_remaining" => $paymentInfo["time_remaining"],
			
			// Status
			"status" => $paymentInfo["status"],
			
			// Recipient payment proof address
			"recipient_payment_proof_address" => $paymentInfo["recipient_payment_proof_address"]
		];
	}
	
	// Get price
	public function getPrice(?string $apiKey = NULL): string | FALSE | NULL {
	
		// Check if sending get price request to the private server failed
		$getPriceResponse = @file_get_contents($this->privateServer . "/get_price?" . http_build_query(array_filter([
		
			// API key
			"api_key" => $apiKey
			
		], function(string | int | NULL $value): bool {
		
			// Return if value isn't null
			return $value !== NULL;
		})));
		
		if($getPriceResponse === FALSE) {
		
			// Check if an error occurred on the private server
			if(isset($http_response_header) === FALSE || is_array($http_response_header) === FALSE || count($http_response_header) <= 0 || preg_match('/HTTP\/[^ ]+ (\d+)/u', $http_response_header[0], $statusCode) !== 1 || $statusCode[1] === "500") {
			
				// Return false
				return FALSE;
			}
			
			// Otherwise assume request was invalid
			else {
			
				// Return null
				return NULL;
			}
		}
		
		// Try
		try {
		
			// Get price from get price response
			$price = json_decode($getPriceResponse, TRUE, 2, JSON_THROW_ON_ERROR);
		}
		
		// Catch errors
		catch(\Throwable $error) {
		
			// Return false
			return FALSE;
		}
		
		// Check if price is invalid
		if(is_array($price) === FALSE || array_key_exists("price", $price) === FALSE || is_string($price["price"]) === FALSE || preg_match('/^(?:0(?:\.\d+)?|[1-9]\d*(?:\.\d+)?)$/u', $price["price"]) !== 1) {
		
			// Return false
			return FALSE;
		}
		
		// Return price
		return $price["price"];
	}
	
	// Get public server info
	public function getPublicServerInfo(?string $apiKey = NULL): array | FALSE | NULL {
	
		// Check if sending get public server info request to the private server failed
		$getPublicServerInfoResponse = @file_get_contents($this->privateServer . "/get_public_server_info?" . http_build_query(array_filter([
		
			// API key
			"api_key" => $apiKey
			
		], function(string | int | NULL $value): bool {
		
			// Return if value isn't null
			return $value !== NULL;
		})));
		
		if($getPublicServerInfoResponse === FALSE) {
		
			// Check if an error occurred on the private server
			if(isset($http_response_header) === FALSE || is_array($http_response_header) === FALSE || count($http_response_header) <= 0 || preg_match('/HTTP\/[^ ]+ (\d+)/u', $http_response_header[0], $statusCode) !== 1 || $statusCode[1] === "500") {
			
				// Return false
				return FALSE;
			}
			
			// Otherwise assume request was invalid
			else {
			
				// Return null
				return NULL;
			}
		}
		
		// Try
		try {
		
			// Get public server info from get public server info response
			$publicServerInfo = json_decode($getPublicServerInfoResponse, TRUE, 2, JSON_THROW_ON_ERROR);
		}
		
		// Catch errors
		catch(\Throwable $error) {
		
			// Return false
			return FALSE;
		}
		
		// Check if public server info's URL or Onion Service address are invalid
		if(is_array($publicServerInfo) === FALSE || array_key_exists("url", $publicServerInfo) === FALSE || is_string($publicServerInfo["url"]) === FALSE || $publicServerInfo["url"] === "" || array_key_exists("onion_service_address", $publicServerInfo) === FALSE || ($publicServerInfo["onion_service_address"] !== NULL && is_string($publicServerInfo["onion_service_address"]) === FALSE) || ($publicServerInfo["onion_service_address"] !== NULL && $publicServerInfo["onion_service_address"] === "")) {
		
			// Return false
			return FALSE;
		}
		
		// Return public server info's URL and Onion Service address
		return [
		
			// URL
			"url" => $publicServerInfo["url"],
			
			// Onion Service address
			"onion_service_address" => $publicServerInfo["onion_service_address"]
		];
	}
}


?>
