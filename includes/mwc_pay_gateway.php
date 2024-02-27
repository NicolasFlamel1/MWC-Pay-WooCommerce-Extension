<?php


// Enforce strict types
declare(strict_types=1);


// Check if file is accessed directly
if(defined("ABSPATH") === FALSE) {

	// Exit
	exit;
}

// Check if MWC Pay WooCommerce extension class exists, plugin basename and data exist, and MWC Pay session handler, cart, and gateway classes don't exist but their base classes do
if(class_exists("MwcPayWooCommerceExtension") === TRUE && isset($pluginBasename) === TRUE && isset($pluginData) === TRUE && class_exists("WC_Session_Handler") === TRUE && class_exists("WC_Session_Handler_MWC_Pay") === FALSE && class_exists("WC_Cart") === TRUE && class_exists("WC_Cart_MWC_Pay") === FALSE && class_exists("WC_Payment_Gateway") === TRUE && class_exists("WC_Gateway_MWC_Pay") === FALSE) {

	// MWC Pay session handler class
	final class WC_Session_Handler_MWC_Pay extends WC_Session_Handler {
	
		// Session
		private mixed $WC_Session_Handler_MWC_Pay_session;
		
		// Constructor
		public function __construct(string $sessionCookie) {
		
			// Call parent constructor
			parent::__construct();
			
			// Save session
			$this->WC_Session_Handler_MWC_Pay_session = WC()->session;
			
			// Check if cookies don't exist
			if(isset($_COOKIE) === FALSE) {
			
				// Create cookies
				$_COOKIE = [];
			}
			
			// Set session cookie to be the provided session cookie
			$_COOKIE[$this->_cookie] = $sessionCookie;
			
			// Initialize session cookie
			$this->init_session_cookie();
			
			// Set session to self
			WC()->session = $this;
		}
		
		// Destructor
		public function __destruct() {
		
			// Remove session cookie
			unset($_COOKIE[$this->_cookie]);
			
			// Restore session
			WC()->session = $this->WC_Session_Handler_MWC_Pay_session;
		}
		
		// Destroy session
		public function destroy_session(): void {
		
			// Do nothing
		}
		
		// Set session expiration
		public function set_session_expiration(): void {
		
			// Do nothing
		}
	}
	
	// MWC Pay cart class
	final class WC_Cart_MWC_Pay extends WC_Cart {
	
		// Session
		private WC_Session_Handler_MWC_Pay $WC_Cart_MWC_Pay_session;
		
		// Constructor
		public function __construct(string $sessionCookie) {
		
			// Call parent constructor
			parent::__construct();
			
			// Use session from the session cookie
			$this->WC_Cart_MWC_Pay_session = new WC_Session_Handler_MWC_Pay($sessionCookie);
			
			// Load cart from session
			$this->session->get_cart_from_session();
		}
	}
	
	// MWC Pay gateway class
	final class WC_Gateway_MWC_Pay extends WC_Payment_Gateway {
	
		// ID
		private const WC_Gateway_MWC_Pay_ID = "mwc_pay";
		
		// MimbleWimble Coin currency ID
		private const WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID = "MWC";
		
		// MimbleWimble Coin currency symbol
		private const WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_SYMBOL = "MWC";
		
		// MimbleWimble Coin number of decimal digits
		private const WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS = MwcPayWooCommerceExtension::MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS;
		
		// MimbleWimble Coin block explorer URL
		private const WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_BLOCK_EXPLORER_URL = "https://explorer.mwc.mw/#k";
		
		// URL protocol
		private const WC_Gateway_MWC_Pay_URL_PROTOCOL = "web+mwc";
		
		// Nonce name
		private const WC_Gateway_MWC_Pay_NONCE_NAME = self::WC_Gateway_MWC_Pay_ID . "_nonce";
		
		// Secret number of bytes
		private const WC_Gateway_MWC_Pay_SECRET_NUMBER_OF_BYTES = 256 / 8;
		
		// Seconds in a minute
		private const WC_Gateway_MWC_Pay_SECONDS_IN_A_MINUTE = 60;
		
		// Minutes in an hour
		private const WC_Gateway_MWC_Pay_MINUTES_IN_AN_HOUR = 60;
		
		// Hours in a day
		private const WC_Gateway_MWC_Pay_HOURS_IN_A_DAY = 24;
		
		// Payment request timeout seconds
		private const WC_Gateway_MWC_Pay_PAYMENT_REQUEST_TIMEOUT_SECONDS = 1 * self::WC_Gateway_MWC_Pay_SECONDS_IN_A_MINUTE;
		
		// Uint32 max
		private const WC_Gateway_MWC_Pay_UINT32_MAX = 0xFFFFFFFF;
		
		// Default icon type
		private const WC_Gateway_MWC_Pay_DEFAULT_ICON_TYPE = "light";
		
		// Default display format
		private const WC_Gateway_MWC_Pay_DEFAULT_DISPLAY_FORMAT = "icon";
		
		// Default private server URL
		private const WC_Gateway_MWC_Pay_DEFAULT_PRIVATE_SERVER_URL = "http://localhost:9010";
		
		// Default discount or surcharge percent
		private const WC_Gateway_MWC_Pay_DEFAULT_DISCOUNT_OR_SURCHARGE_PERCENT = 0;
		
		// Default order timeout
		private const WC_Gateway_MWC_Pay_DEFAULT_ORDER_TIMEOUT = 10 * self::WC_Gateway_MWC_Pay_SECONDS_IN_A_MINUTE;
		
		// Default payment required number of block confirmations per USD value
		private const WC_Gateway_MWC_Pay_DEFAULT_PAYMENT_REQUIRED_NUMBER_OF_BLOCK_CONFIRMATIONS_PER_USD_VALUE = 0.5;
		
		// Default payment minimum required number of block confirmations
		private const WC_Gateway_MWC_Pay_DEFAULT_PAYMENT_MINIMUM_REQUIRED_NUMBER_OF_BLOCK_CONFIRMATIONS = 30;
		
		// Default payment maximum required number of block confirmations
		private const WC_Gateway_MWC_Pay_DEFAULT_PAYMENT_MAXIMUM_REQUIRED_NUMBER_OF_BLOCK_CONFIRMATIONS = 1000;
		
		// Default completed payment maximum allowed time since being received
		private const WC_Gateway_MWC_Pay_DEFAULT_COMPLETED_PAYMENT_MAXIMUM_ALLOWED_TIME_SINCE_BEING_RECEIVED = 2 * self::WC_Gateway_MWC_Pay_HOURS_IN_A_DAY * self::WC_Gateway_MWC_Pay_MINUTES_IN_AN_HOUR * self::WC_Gateway_MWC_Pay_SECONDS_IN_A_MINUTE;
		
		// Icon type
		public string $WC_Gateway_MWC_Pay_iconType;
		
		// Display format
		public string $WC_Gateway_MWC_Pay_displayFormat;
		
		// Private server URL
		private string $WC_Gateway_MWC_Pay_privateServerUrl;
		
		// Discount or surcharge percent
		private string $WC_Gateway_MWC_Pay_discountOrSurchargePercent;
		
		// Order timeout
		private string $WC_Gateway_MWC_Pay_orderTimeout;
		
		// Payment required number of block confirmations per USD value
		private string $WC_Gateway_MWC_Pay_paymentRequiredNumberOfBlockConfirmationsPerUsdValue;
		
		// Payment minimum required number of block confirmations
		private string $WC_Gateway_MWC_Pay_paymentMinimumRequiredNumberOfBlockConfirmations;
		
		// Payment maximum required number of block confirmations
		private string $WC_Gateway_MWC_Pay_paymentMaximumRequiredNumberOfBlockConfirmations;
		
		// Completed payment maximum allowed time since being received
		private string $WC_Gateway_MWC_Pay_completedPaymentMaximumAllowedTimeSinceBeingReceived;
		
		// MWC Pay
		private Nicolasflamel\MwcPay\MwcPay $WC_Gateway_MWC_Pay_mwcPay;
		
		// Constructor
		public function __construct() {
		
			// Include dependencies
			require_once plugin_dir_path(__FILE__) . "../vendor/autoload.php";
			
			// Define values
			$this->id = self::WC_Gateway_MWC_Pay_ID;
			$this->has_fields = FALSE;
			$this->method_title = esc_html__("MWC Pay", "mwc-pay-woocommerce-extension");
			$this->method_description = esc_html__("Accept MimbleWimble Coin payments.", "mwc-pay-woocommerce-extension");
			$this->supports = ["products"];
			
			// Initialize settings
			$this->init_form_fields();
			$this->init_settings();
			
			// Get settings
			$this->title = $this->get_option("title");
			$this->icon = ($this->get_option("WC_Gateway_MWC_Pay_icon_type") === "light") ? plugins_url("../assets/images/gateway_icon_light.svg", __FILE__) : plugins_url("../assets/images/gateway_icon_dark.svg", __FILE__);
			$this->description = $this->get_option("description");
			$this->WC_Gateway_MWC_Pay_displayFormat = $this->get_option("WC_Gateway_MWC_Pay_display_format");
			$this->WC_Gateway_MWC_Pay_privateServerUrl = $this->get_option("WC_Gateway_MWC_Pay_private_server_url");
			$this->WC_Gateway_MWC_Pay_discountOrSurchargePercent = $this->get_option("WC_Gateway_MWC_Pay_discount_or_surcharge_percent");
			$this->WC_Gateway_MWC_Pay_orderTimeout = $this->get_option("WC_Gateway_MWC_Pay_order_timeout");
			$this->WC_Gateway_MWC_Pay_paymentRequiredNumberOfBlockConfirmationsPerUsdValue = $this->get_option("WC_Gateway_MWC_Pay_payment_required_number_of_block_confirmations_per_usd_value");
			$this->WC_Gateway_MWC_Pay_paymentMinimumRequiredNumberOfBlockConfirmations = $this->get_option("WC_Gateway_MWC_Pay_payment_minimum_required_number_of_block_confirmations");
			$this->WC_Gateway_MWC_Pay_paymentMaximumRequiredNumberOfBlockConfirmations = $this->get_option("WC_Gateway_MWC_Pay_payment_maximum_required_number_of_block_confirmations");
			$this->WC_Gateway_MWC_Pay_completedPaymentMaximumAllowedTimeSinceBeingReceived = $this->get_option("WC_Gateway_MWC_Pay_completed_payment_maximum_allowed_time_since_being_received");
			
			// Initialize MWC Pay
			$this->WC_Gateway_MWC_Pay_mwcPay = new Nicolasflamel\MwcPay\MwcPay($this->WC_Gateway_MWC_Pay_privateServerUrl);
			
			// Add settings fields
			add_filter("woocommerce_generate_{$this->id}_url_html", [$this, "WC_Gateway_MWC_Pay_addUrlSettingsField"], 10, 3);
			add_filter("woocommerce_generate_{$this->id}_number_html", [$this, "WC_Gateway_MWC_Pay_addNumberSettingsField"], 10, 3);
			
			// Process settings when saved
			add_action("woocommerce_update_options_payment_gateways_{$this->id}", [$this, "process_admin_options"]);
			
			// Warn about insecure connection
			add_action("admin_notices", [$this, "WC_Gateway_MWC_Pay_warnAboutInsecureConnection"]);
			
			// Process API requests
			add_action("woocommerce_api_{$this->id}", [$this, "WC_Gateway_MWC_Pay_processApiRequests"]);
			
			// Add checkout script parameters
			add_action("wp_enqueue_scripts", [$this, "WC_Gateway_MWC_Pay_addCheckoutScriptParameters"]);
			
			// Show price at checkout
			add_filter("woocommerce_cart_totals_order_total_html", [$this, "WC_Gateway_MWC_Pay_showPriceAtCheckout"], 999);
			
			// Apply discount or surchange
			add_filter("woocommerce_cart_calculate_fees", [$this, "WC_Gateway_MWC_Pay_applyDiscountOrSurchange"], 999);
			
			// Apply display format to title
			add_filter("woocommerce_gateway_title", [$this, "WC_Gateway_MWC_Pay_applyDisplayFormatToTitle"], 999, 2);
			
			// Apply display format to icon
			add_filter("woocommerce_gateway_icon", [$this, "WC_Gateway_MWC_Pay_applyDisplayFormatToIcon"], 999, 2);
		}
		
		// Initialize form fields
		public function init_form_fields(): void {
		
			// Set form fields
			$this->form_fields = [
			
				// Enabled
				"enabled" => [
					"title" => esc_html__("Enable/Disable", "woocommerce"),
					"type" => "checkbox",
					"label" => esc_html__("Enable MWC Pay", "mwc-pay-woocommerce-extension"),
					"default" => "yes"
				],
				
				// Title
				"title" => [
					"title" => esc_html__("Title", "woocommerce"),
					"type" => "safe_text",
					"description" => esc_html__("This controls the title which the user sees during checkout.", "woocommerce"),
					"default" => htmlentities(__("MWC Pay", "mwc-pay-woocommerce-extension"), ENT_NOQUOTES, get_bloginfo("charset")),
					"desc_tip" => TRUE
				],
				
				// Icon type
				"WC_Gateway_MWC_Pay_icon_type" => [
					"title" => esc_html__("Icon type", "mwc-pay-woocommerce-extension"),
					"description" => esc_html__("This controls the icon which the user sees during checkout.", "mwc-pay-woocommerce-extension"),
					"type" => "select",
					"options" => [
						"light" => esc_html__("Light", "mwc-pay-woocommerce-extension"),
						"dark" => esc_html__("Dark", "mwc-pay-woocommerce-extension")
					],
					"default" => self::WC_Gateway_MWC_Pay_DEFAULT_ICON_TYPE,
					"desc_tip" => TRUE
				],
				
				// Display format
				"WC_Gateway_MWC_Pay_display_format" => [
					"title" => esc_html__("Display format", "mwc-pay-woocommerce-extension"),
					"description" => esc_html__("Display format for this payment method that the customer will see on your checkout.", "mwc-pay-woocommerce-extension"),
					"type" => "select",
					"options" => [
						"title" => esc_html__("Title", "mwc-pay-woocommerce-extension"),
						"icon" => esc_html__("Icon", "mwc-pay-woocommerce-extension"),
						"title_and_icon" => esc_html__("Title and icon", "mwc-pay-woocommerce-extension")
					],
					"default" => self::WC_Gateway_MWC_Pay_DEFAULT_DISPLAY_FORMAT,
					"desc_tip" => TRUE
				],
				
				// Description
				"description" => [
					"title" => esc_html__("Description", "woocommerce"),
					"type" => "textarea",
					"description" => esc_html__("Payment method description that the customer will see on your checkout.", "woocommerce"),
					"default" => htmlentities(__("Pay with MimbleWimble Coin.", "mwc-pay-woocommerce-extension"), ENT_NOQUOTES, get_bloginfo("charset")),
					"desc_tip" => TRUE
				],
				
				// Private server URL
				"WC_Gateway_MWC_Pay_private_server_url" => [
					"title" => esc_html__("Private server URL", "mwc-pay-woocommerce-extension"),
					"type" => "{$this->id}_url",
					"description" => esc_html__("URL that your MWC Pay private server is listening at.", "mwc-pay-woocommerce-extension"),
					"default" => self::WC_Gateway_MWC_Pay_DEFAULT_PRIVATE_SERVER_URL,
					"desc_tip" => TRUE
				],
				
				// Discount or surcharge percent
				"WC_Gateway_MWC_Pay_discount_or_surcharge_percent" => [
					"title" => esc_html__("Discount or surcharge percent", "mwc-pay-woocommerce-extension"),
					"type" => "{$this->id}_number",
					"description" => esc_html__("Percent change applied to an order's total when paying with this payment method.", "mwc-pay-woocommerce-extension"),
					"default" => (string)self::WC_Gateway_MWC_Pay_DEFAULT_DISCOUNT_OR_SURCHARGE_PERCENT,
					"desc_tip" => TRUE,
					"{$this->id}_value_type" => "decimal",
					"{$this->id}_value_minimum" => (string)-100,
					"{$this->id}_value_maximum" => (string)100
				],
				
				// Order timeout
				"WC_Gateway_MWC_Pay_order_timeout" => [
					"title" => esc_html__("Order timeout", "mwc-pay-woocommerce-extension"),
					"type" => "{$this->id}_number",
					"description" => esc_html__("Number of seconds that a customer has to pay for an order before it expires.", "mwc-pay-woocommerce-extension"),
					"default" => (string)self::WC_Gateway_MWC_Pay_DEFAULT_ORDER_TIMEOUT,
					"desc_tip" => TRUE,
					"{$this->id}_value_type" => "numeric",
					"{$this->id}_value_minimum" => (string)1,
					"{$this->id}_value_maximum" => (string)self::WC_Gateway_MWC_Pay_UINT32_MAX
				],
				
				// Payment required number of block confirmations per USD value
				"WC_Gateway_MWC_Pay_payment_required_number_of_block_confirmations_per_usd_value" => [
					"title" => esc_html__("Payment required number of block confirmations per USD value", "mwc-pay-woocommerce-extension"),
					"type" => "{$this->id}_number",
					"description" => esc_html__("Number of block confirmations per an order's value in USD that an order's payment must achieve for that order to start processing.", "mwc-pay-woocommerce-extension"),
					"default" => (string)self::WC_Gateway_MWC_Pay_DEFAULT_PAYMENT_REQUIRED_NUMBER_OF_BLOCK_CONFIRMATIONS_PER_USD_VALUE,
					"desc_tip" => TRUE,
					"{$this->id}_value_type" => "decimal",
					"{$this->id}_value_minimum" => (string)0
				],
				
				// Payment minimum required number of block confirmations
				"WC_Gateway_MWC_Pay_payment_minimum_required_number_of_block_confirmations" => [
					"title" => esc_html__("Payment minimum required number of block confirmations", "mwc-pay-woocommerce-extension"),
					"type" => "{$this->id}_number",
					"description" => esc_html__("Minimum number of block confirmations that an order's payment must achieve for that order to start processing.", "mwc-pay-woocommerce-extension"),
					"default" => (string)self::WC_Gateway_MWC_Pay_DEFAULT_PAYMENT_MINIMUM_REQUIRED_NUMBER_OF_BLOCK_CONFIRMATIONS,
					"desc_tip" => TRUE,
					"{$this->id}_value_type" => "numeric",
					"{$this->id}_value_minimum" => (string)1,
					"{$this->id}_value_maximum" => (string)self::WC_Gateway_MWC_Pay_UINT32_MAX
				],
				
				// Payment maximum required number of block confirmations
				"WC_Gateway_MWC_Pay_payment_maximum_required_number_of_block_confirmations" => [
					"title" => esc_html__("Payment maximum required number of block confirmations", "mwc-pay-woocommerce-extension"),
					"type" => "{$this->id}_number",
					"description" => esc_html__("Maximum number of block confirmations that an order's payment must achieve for that order to start processing.", "mwc-pay-woocommerce-extension"),
					"default" => (string)self::WC_Gateway_MWC_Pay_DEFAULT_PAYMENT_MAXIMUM_REQUIRED_NUMBER_OF_BLOCK_CONFIRMATIONS,
					"desc_tip" => TRUE,
					"{$this->id}_value_type" => "numeric",
					"{$this->id}_value_minimum" => (string)1,
					"{$this->id}_value_maximum" => (string)self::WC_Gateway_MWC_Pay_UINT32_MAX
				],
				
				// Completed payment maximum allowed time since being received
				"WC_Gateway_MWC_Pay_completed_payment_maximum_allowed_time_since_being_received" => [
					"title" => esc_html__("Completed payment maximum allowed time since being received", "mwc-pay-woocommerce-extension"),
					"type" => "{$this->id}_number",
					"description" => esc_html__("Maximum number of seconds that a received payment has to achieve its required number of block confirmations. Orders with a payment that exceeds this time limit will be marked as failed when it achieves its required number of block confirmations.", "mwc-pay-woocommerce-extension"),
					"default" => (string)self::WC_Gateway_MWC_Pay_DEFAULT_COMPLETED_PAYMENT_MAXIMUM_ALLOWED_TIME_SINCE_BEING_RECEIVED,
					"desc_tip" => TRUE,
					"{$this->id}_value_type" => "numeric",
					"{$this->id}_value_minimum" => (string)1
				]
			];
		}
		
		// Validate title field
		public function validate_title_field(string $key, string $value): string {
		
			// Validate value as a safe text field
			$value = $this->validate_safe_text_field($key, $value);
			
			// Check if value doesn't exist
			if($value === "") {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Title is required.", "mwc-pay-woocommerce-extension"));
				
				// Return title setting
				return $this->get_option("title");
			}
			
			// Otherwise
			else {
			
				// Return value
				return $value;
			}
		}
		
		// Validate icon type field
		public function validate_WC_Gateway_MWC_Pay_icon_type_field(string $key, string $value): string {
		
			// Validate value as a text field
			$value = $this->validate_text_field($key, $value);
			
			// Check if value doesn't exist
			if($value === "") {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Icon type is required.", "mwc-pay-woocommerce-extension"));
				
				// Return icon type setting
				return $this->get_option("WC_Gateway_MWC_Pay_icon_type");
			}
			
			// Otherwise check if value is invalid
			else if($value !== "light" && $value !== "dark") {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Icon type is invalid.", "mwc-pay-woocommerce-extension"));
				
				// Return icon type setting
				return $this->get_option("WC_Gateway_MWC_Pay_icon_type");
			}
			
			// Otherwise
			else {
			
				// Return value
				return $value;
			}
		}
		
		// Validate display format field
		public function validate_WC_Gateway_MWC_Pay_display_format_field(string $key, string $value): string {
		
			// Validate value as a text field
			$value = $this->validate_text_field($key, $value);
			
			// Check if value doesn't exist
			if($value === "") {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Display format is required.", "mwc-pay-woocommerce-extension"));
				
				// Return display format setting
				return $this->get_option("WC_Gateway_MWC_Pay_display_format");
			}
			
			// Otherwise check if value is invalid
			else if($value !== "title" && $value !== "icon" && $value !== "title_and_icon") {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Display format is invalid.", "mwc-pay-woocommerce-extension"));
				
				// Return display format setting
				return $this->get_option("WC_Gateway_MWC_Pay_display_format");
			}
			
			// Otherwise
			else {
			
				// Return value
				return $value;
			}
		}
		
		// Validate private server URL field
		public function validate_WC_Gateway_MWC_Pay_private_server_url_field(string $key, string $value): string {
		
			// Validate value as a text field
			$value = $this->validate_text_field($key, $value);
			
			// Check if value doesn't exist
			if($value === "") {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Private server URL is required.", "mwc-pay-woocommerce-extension"));
				
				// Return private server URL setting
				return $this->get_option("WC_Gateway_MWC_Pay_private_server_url");
			}
			
			// Otherwise check if value is invalid
			else if(preg_match('/^https?:\/\/.+$/ui', $value) !== 1) {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Private server URL is invalid.", "mwc-pay-woocommerce-extension"));
				
				// Return private server URL setting
				return $this->get_option("WC_Gateway_MWC_Pay_private_server_url");
			}
			
			// Otherwise
			else {
			
				// Check if value isn't the same as the current private server URL setting
				if($value !== $this->get_option("WC_Gateway_MWC_Pay_private_server_url")) {
			
					// Check if getting a valid response from the MWC Pay private server listening at the value failed
					$privateServerResponse = (new Nicolasflamel\MwcPay\MwcPay($value))->getPrice();
					if($privateServerResponse === FALSE || $privateServerResponse === NULL) {
					
						// Display error
						WC_Admin_Settings::add_error(esc_html__("Getting a valid response from the MWC Pay private server listening at the private server URL failed.", "mwc-pay-woocommerce-extension"));
						
						// Return private server URL setting
						return $this->get_option("WC_Gateway_MWC_Pay_private_server_url");
					}
				}
				
				// Return value
				return $value;
			}
		}
		
		// Validate discount or surcharge percent field
		public function validate_WC_Gateway_MWC_Pay_discount_or_surcharge_percent_field(string $key, string $value): string {
		
			// Validate value as a text field
			$value = $this->validate_text_field($key, $value);
			
			// Check if value doesn't exist
			if($value === "" || $value === "-") {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Discount or surcharge percent is required.", "mwc-pay-woocommerce-extension"));
				
				// Return discount or surcharge percent setting
				return $this->get_option("WC_Gateway_MWC_Pay_discount_or_surcharge_percent");
			}
			
			// Otherwise check if value is invalid
			else if(preg_match('/^-?(?:0|[1-9]\d*)?(?:\.\d+)?$/u', $value) !== 1 || Brick\Math\BigDecimal::of($value)->isLessThan(-100) === TRUE || Brick\Math\BigDecimal::of($value)->isGreaterThan(100) === TRUE) {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Discount or surcharge percent is invalid.", "mwc-pay-woocommerce-extension"));
				
				// Return discount or surcharge percent setting
				return $this->get_option("WC_Gateway_MWC_Pay_discount_or_surcharge_percent");
			}
			
			// Otherwise
			else {
			
				// Check if value doesn't have a fractional part
				if(preg_match('/\./u', $value) !== 1) {
				
					// Return value
					return $value;
				}
				
				// Otherwise
				else {
			
					// Return value with trailing zeros and decimal point removed
					return rtrim(rtrim((string)Brick\Math\BigDecimal::of($value), "0"), ".");
				}
			}
		}
		
		// Validate order timeout field
		public function validate_WC_Gateway_MWC_Pay_order_timeout_field(string $key, string $value): string {
		
			// Validate value as a text field
			$value = $this->validate_text_field($key, $value);
			
			// Check if value doesn't exist
			if($value === "") {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Order timeout is required.", "mwc-pay-woocommerce-extension"));
				
				// Return order timeout setting
				return $this->get_option("WC_Gateway_MWC_Pay_order_timeout");
			}
			
			// Otherwise check if value is invalid
			else if(preg_match('/^[1-9]\d*$/u', $value) !== 1 || Brick\Math\BigInteger::of($value)->isGreaterThan(self::WC_Gateway_MWC_Pay_UINT32_MAX) === TRUE) {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Order timeout is invalid.", "mwc-pay-woocommerce-extension"));
				
				// Return order timeout setting
				return $this->get_option("WC_Gateway_MWC_Pay_order_timeout");
			}
			
			// Otherwise
			else {
			
				// Return value
				return $value;
			}
		}
		
		// Validate payment required number of block confirmations per USD value field
		public function validate_WC_Gateway_MWC_Pay_payment_required_number_of_block_confirmations_per_usd_value_field(string $key, string $value): string {
		
			// Validate value as a text field
			$value = $this->validate_text_field($key, $value);
			
			// Check if value doesn't exist
			if($value === "") {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Payment required number of block confirmations per USD value is required.", "mwc-pay-woocommerce-extension"));
				
				// Return payment required number of block confirmations per USD value setting
				return $this->get_option("WC_Gateway_MWC_Pay_payment_required_number_of_block_confirmations_per_usd_value");
			}
			
			// Otherwise check if value is invalid
			else if(preg_match('/^(?:0|[1-9]\d*)?(?:\.\d+)?$/u', $value) !== 1) {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Payment required number of block confirmations per USD value is invalid.", "mwc-pay-woocommerce-extension"));
				
				// Return payment required number of block confirmations per USD value setting
				return $this->get_option("WC_Gateway_MWC_Pay_payment_required_number_of_block_confirmations_per_usd_value");
			}
			
			// Otherwise
			else {
			
				// Check if value doesn't have a fractional part
				if(preg_match('/\./u', $value) !== 1) {
				
					// Return value
					return $value;
				}
				
				// Otherwise
				else {
			
					// Return value with trailing zeros and decimal point removed
					return rtrim(rtrim((string)Brick\Math\BigDecimal::of($value), "0"), ".");
				}
			}
		}
		
		// Validate payment minimum required number of block confirmations field
		public function validate_WC_Gateway_MWC_Pay_payment_minimum_required_number_of_block_confirmations_field(string $key, string $value): string {
		
			// Validate value as a text field
			$value = $this->validate_text_field($key, $value);
			
			// Check if value doesn't exist
			if($value === "") {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Payment minimum required number of block confirmations is required.", "mwc-pay-woocommerce-extension"));
				
				// Return payment minimum required number of block confirmations setting
				return $this->get_option("WC_Gateway_MWC_Pay_payment_minimum_required_number_of_block_confirmations");
			}
			
			// Otherwise check if value is invalid
			else if(preg_match('/^[1-9]\d*$/u', $value) !== 1 || Brick\Math\BigInteger::of($value)->isGreaterThan(self::WC_Gateway_MWC_Pay_UINT32_MAX) === TRUE) {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Payment minimum required number of block confirmations is invalid.", "mwc-pay-woocommerce-extension"));
				
				// Return payment minimum required number of block confirmations setting
				return $this->get_option("WC_Gateway_MWC_Pay_payment_minimum_required_number_of_block_confirmations");
			}
			
			// Otherwise
			else {
			
				// Return value
				return $value;
			}
		}
		
		// Validate payment maximum required number of block confirmations field
		public function validate_WC_Gateway_MWC_Pay_payment_maximum_required_number_of_block_confirmations_field(string $key, string $value): string {
		
			// Validate value as a text field
			$value = $this->validate_text_field($key, $value);
			
			// Check if value doesn't exist
			if($value === "") {
			
				// Check if payment maximum required number of block confirmations setting is less than the payment minimum required number of block confirmations setting
				if(Brick\Math\BigInteger::of($this->get_option("WC_Gateway_MWC_Pay_payment_maximum_required_number_of_block_confirmations"))->isLessThan($this->get_option("WC_Gateway_MWC_Pay_payment_minimum_required_number_of_block_confirmations")) === TRUE) {
				
					// Display message
					WC_Admin_Settings::add_message(esc_html__("Payment maximum required number of block confirmations was increased to be equal to the payment minimum required number of block confirmations.", "mwc-pay-woocommerce-extension"));
					
					// Return the payment minimum required number of block confirmations setting
					return $this->get_option("WC_Gateway_MWC_Pay_payment_minimum_required_number_of_block_confirmations");
				}
				
				// Otherwise
				else {
				
					// Display error
					WC_Admin_Settings::add_error(esc_html__("Payment maximum required number of block confirmations is required.", "mwc-pay-woocommerce-extension"));
				
					// Return payment maximum required number of block confirmations setting
					return $this->get_option("WC_Gateway_MWC_Pay_payment_maximum_required_number_of_block_confirmations");
				}
			}
			
			// Otherwise check if value is invalid
			else if(preg_match('/^[1-9]\d*$/u', $value) !== 1 || Brick\Math\BigInteger::of($value)->isGreaterThan(self::WC_Gateway_MWC_Pay_UINT32_MAX) === TRUE) {
				
				// Check if payment maximum required number of block confirmations setting is less than the payment minimum required number of block confirmations setting
				if(Brick\Math\BigInteger::of($this->get_option("WC_Gateway_MWC_Pay_payment_maximum_required_number_of_block_confirmations"))->isLessThan($this->get_option("WC_Gateway_MWC_Pay_payment_minimum_required_number_of_block_confirmations")) === TRUE) {
				
					// Display message
					WC_Admin_Settings::add_message(esc_html__("Payment maximum required number of block confirmations was increased to be equal to the payment minimum required number of block confirmations.", "mwc-pay-woocommerce-extension"));
					
					// Return the payment minimum required number of block confirmations setting
					return $this->get_option("WC_Gateway_MWC_Pay_payment_minimum_required_number_of_block_confirmations");
				}
				
				// Otherwise
				else {
				
					// Display error
					WC_Admin_Settings::add_error(esc_html__("Payment maximum required number of block confirmations is invalid.", "mwc-pay-woocommerce-extension"));
					
					// Return payment maximum required number of block confirmations setting
					return $this->get_option("WC_Gateway_MWC_Pay_payment_maximum_required_number_of_block_confirmations");
				}
			}
			
			// Otherwise
			else {
			
				// Check if value is less than the payment minimum required number of block confirmations setting
				if(Brick\Math\BigInteger::of($value)->isLessThan($this->get_option("WC_Gateway_MWC_Pay_payment_minimum_required_number_of_block_confirmations")) === TRUE) {
				
					// Display message
					WC_Admin_Settings::add_message(esc_html__("Payment maximum required number of block confirmations was increased to be equal to the payment minimum required number of block confirmations.", "mwc-pay-woocommerce-extension"));
					
					// Return the payment minimum required number of block confirmations setting
					return $this->get_option("WC_Gateway_MWC_Pay_payment_minimum_required_number_of_block_confirmations");
				}
				
				// Otherwise
				else {
				
					// Return value
					return $value;
				}
			}
		}
		
		// Validate completed payment maximum allowed time since being received field
		public function validate_WC_Gateway_MWC_Pay_completed_payment_maximum_allowed_time_since_being_received_field(string $key, string $value): string {
		
			// Validate value as a text field
			$value = $this->validate_text_field($key, $value);
			
			// Check if value doesn't exist
			if($value === "") {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Completed payment maximum allowed time since being received is required.", "mwc-pay-woocommerce-extension"));
				
				// Return completed payment maximum allowed time since being received setting
				return $this->get_option("WC_Gateway_MWC_Pay_completed_payment_maximum_allowed_time_since_being_received");
			}
			
			// Otherwise check if value is invalid
			else if(preg_match('/^[1-9]\d*$/u', $value) !== 1) {
			
				// Display error
				WC_Admin_Settings::add_error(esc_html__("Completed payment maximum allowed time since being received is invalid.", "mwc-pay-woocommerce-extension"));
				
				// Return completed payment maximum allowed time since being received setting
				return $this->get_option("WC_Gateway_MWC_Pay_completed_payment_maximum_allowed_time_since_being_received");
			}
			
			// Otherwise
			else {
			
				// Return value
				return $value;
			}
		}
		
		// Process payment
		public function process_payment(mixed $orderId): array {
		
			// Get order
			$order = wc_get_order($orderId);
			
			// Check if order doesn't exist or isn't using this payment method
			if($order === FALSE || $order->get_payment_method() !== $this->id) {
			
				// Throw error
				throw new Exception(esc_html__("Unable to create order.", "woocommerce"));
			}
			
			// Otherwise
			else {
			
				// Check if order failed for taking too long to achieve enough block confirmations
				if($order->get_meta("_{$this->id}_can_be_processed") !== "") {
				
					// Check if order isn't failed or deleted
					if($order->has_status(["failed", "trash"]) === FALSE) {
					
						// Set that order failed
						$order->update_status("failed");
					}
					
					// Try
					try {
					
						// Don't allow resuming order
						WC()->session->set("order_awaiting_payment", FALSE);
						WC()->session->set("store_api_draft_order", 0);
						WC()->session->save_data();
					}
					
					// Catch errors
					catch(\Exception $error) {
					
					}
					
					// Throw error
					throw new Exception(esc_html__("Unable to create order.", "woocommerce"));
				}
			
				// Check if placing order failed previously
				if($order->has_status("failed") === TRUE) {
				
					// Set that order is pending payment
					$order->set_status("pending");
				}
				
				// Check if order isn't pending payment or was already created
				if($order->has_status("pending") === FALSE || $order->get_meta("_{$this->id}_secret") !== "") {
				
					// Check if order is pending payment, request isn't using AJAX, and at order pay page
					if($order->has_status("pending") === TRUE && wp_doing_ajax() === FALSE && (isset($_SERVER) === FALSE || array_key_exists("HTTP_X_REQUESTED_WITH", $_SERVER) === FALSE || is_string($_SERVER["HTTP_X_REQUESTED_WITH"]) === FALSE || preg_match('/^XMLHttpRequest$/ui', $_SERVER["HTTP_X_REQUESTED_WITH"]) !== 1) && is_wc_endpoint_url("order-pay") === TRUE) {
					
						// Throw error
						throw new Exception(sprintf(esc_html__('%1$s can\'t process payments at this page. Please recreate your order and go to the %2$scheckout page%3$s to continue.', "mwc-pay-woocommerce-extension"), esc_html($this->title), "<a href=\"" . esc_url(wc_get_checkout_url()) . "\" aria-label=\"" . esc_attr__("Go to checkout page", "mwc-pay-woocommerce-extension") . "\">", "</a>"));
					}
					
					// Otherwise
					else {
					
						// Try
						try {
						
							// Don't allow resuming order
							WC()->session->set("order_awaiting_payment", FALSE);
							WC()->session->set("store_api_draft_order", 0);
							WC()->session->save_data();
						}
						
						// Catch errors
						catch(\Exception $error) {
						
						}
						
						// Throw error
						throw new Exception(esc_html__("Unable to create order.", "woocommerce"));
					}
				}
				
				// Otherwise check if request isn't using AJAX
				else if(wp_doing_ajax() === FALSE && (isset($_SERVER) === FALSE || array_key_exists("HTTP_X_REQUESTED_WITH", $_SERVER) === FALSE || is_string($_SERVER["HTTP_X_REQUESTED_WITH"]) === FALSE || preg_match('/^XMLHttpRequest$/ui', $_SERVER["HTTP_X_REQUESTED_WITH"]) !== 1)) {
				
					// Check if at order pay page
					if(is_wc_endpoint_url("order-pay") === TRUE) {
					
						// Throw error
						throw new Exception(sprintf(esc_html__('%1$s can\'t process payments at this page. Please recreate your order and go to the %2$scheckout page%3$s to continue.', "mwc-pay-woocommerce-extension"), esc_html($this->title), "<a href=\"" . esc_url(wc_get_checkout_url()) . "\" aria-label=\"" . esc_attr__("Go to checkout page", "mwc-pay-woocommerce-extension") . "\">", "</a>"));
					}
					
					// Otherwise
					else {
				
						// Set that order failed
						$order->update_status("failed", esc_html__("Customer didn't have JavaScript enabled.", "mwc-pay-woocommerce-extension"));
						
						// Throw error
						throw new Exception(sprintf(esc_html__("Paying with %s requires having JavaScript enabled. Please enable JavaScript to continue.", "mwc-pay-woocommerce-extension"), esc_html($this->title)));
					}
				}
				
				// Otherwise check if order doesn't require a payment
				else if($order->get_total() <= 0) {
				
					// Check if setting that order is complete failed
					if($order->payment_complete() === FALSE) {
					
						// Set that order failed
						$order->update_status("failed", esc_html__("Completing this order failed.", "mwc-pay-woocommerce-extension"));
						
						// Throw error
						throw new Exception(esc_html__("Unable to create order.", "woocommerce"));
					}
					
					// Otherwise
					else {
					
						// Add note to order
						$order->add_order_note(esc_html__("Order didn't require a payment.", "mwc-pay-woocommerce-extension"));
						
						// Try
						try {
						
							// Empty cart
							WC()->cart->empty_cart();
							WC()->session->save_data();
						}
						
						// Catch errors
						catch(\Exception $error) {
						
						}
						
						// Return
						return [
						
							// Result
							"result" => "success",
							
							// Redirect
							"redirect" => $this->get_return_url($order)
						];
					}
				}
				
				// Otherwise
				else {
				
					// Get price
					$price = $this->WC_Gateway_MWC_Pay_getPrice();
					
					// Check if getting price failed
					if($price === FALSE || $price === NULL) {
					
						// Set that order failed
						$order->update_status("failed", esc_html__("Getting MimbleWimble Coin's current price from your MWC Pay private server failed.", "mwc-pay-woocommerce-extension"));
						
						// Throw error
						throw new Exception(esc_html__("Unable to create order.", "woocommerce"));
					}
					
					// Otherwise
					else {
					
						// Try
						try {
						
							// Check if order's currency is MimbleWimble Coin
							if($order->get_currency() === self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID) {
							
								// Get order's value in USD
								$valueInUsd = Brick\Math\BigDecimal::of($order->get_total())->multipliedBy($price);
								
								// Get order's price in MimbleWimble Coin
								$priceInMwc = rtrim(rtrim(sprintf("%." . self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS . "F", $order->get_total()), "0"), ".");
							}
							
							// Otherwise
							else {
							
								// Get order's value in USD
								$valueInUsd = Brick\Math\BigDecimal::of($order->get_total());
								
								// Check if order's currency isn't US dollar
								if($order->get_currency() !== "USD") {
								
									// Check if exchange rate for order's currency isn't known
									$exchangeRate = MwcPayWooCommerceExtension::getCurrencyExchangeRate($order->get_currency());
									if($exchangeRate === NULL) {
									
										// Throw exception
										throw new Exception();
									}
									
									// Check if exchange rate for order's currency is negative or zero
									$exchangeRate = Brick\Math\BigDecimal::of($exchangeRate);
									if($exchangeRate->isNegativeOrZero() === TRUE) {
									
										// Throw exception
										throw new Exception();
									}
									
									// Apply exchange rate to the value in USD
									$valueInUsd = $valueInUsd->dividedBy($exchangeRate, wc_get_price_decimals() + $exchangeRate->getScale(), Brick\Math\RoundingMode::UP);
								}
								
								// Get order's price in MimbleWimble Coin
								$priceInMwc = rtrim(rtrim((string)$valueInUsd->dividedBy($price, self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS, Brick\Math\RoundingMode::UP), "0"), ".");
							}
							
							// Get order's required number of block confirmations from the order's value in USD
							$requiredNumberOfBlockConfirmations = $valueInUsd->multipliedBy($this->WC_Gateway_MWC_Pay_paymentRequiredNumberOfBlockConfirmationsPerUsdValue)->toScale(0, Brick\Math\RoundingMode::UP)->toBigInteger();
							
							// Check if order's required number of block confirmations is less than the payment minimum required number of block confirmations
							if($requiredNumberOfBlockConfirmations->isLessThan($this->WC_Gateway_MWC_Pay_paymentMinimumRequiredNumberOfBlockConfirmations) === TRUE) {
							
								// Set order's required number of block confirmations to the payment minimum required number of block confirmations
								$requiredNumberOfBlockConfirmations = Brick\Math\BigInteger::of($this->WC_Gateway_MWC_Pay_paymentMinimumRequiredNumberOfBlockConfirmations);
							}
							
							// Otherwise check if order's required number of block confirmations is greater than the payment maximum required number of block confirmations
							else if($requiredNumberOfBlockConfirmations->isGreaterThan($this->WC_Gateway_MWC_Pay_paymentMaximumRequiredNumberOfBlockConfirmations) === TRUE) {
							
								// Set order's required number of block confirmations to the payment maximum required number of block confirmations
								$requiredNumberOfBlockConfirmations = Brick\Math\BigInteger::of($this->WC_Gateway_MWC_Pay_paymentMaximumRequiredNumberOfBlockConfirmations);
							}
							
							// Get order's required number of block confirmations as an integer
							$requiredNumberOfBlockConfirmations = $requiredNumberOfBlockConfirmations->toInt();
							
							// Get order's timeout as an integer
							$timeout = Brick\Math\BigInteger::of($this->WC_Gateway_MWC_Pay_orderTimeout)->toInt();
						}
						
						// Catch errors
						catch(\Exception $error) {
						
							// Set that order failed
							$order->update_status("failed", esc_html__("Calculating this order's price, required number of block confirmations, and/or timeout failed.", "mwc-pay-woocommerce-extension"));
							
							// Throw error
							throw new Exception(esc_html__("Unable to create order.", "woocommerce"));
						}
						
						// Try
						try {
						
							// Create order's random secret
							$secret = bin2hex(random_bytes(self::WC_Gateway_MWC_Pay_SECRET_NUMBER_OF_BYTES));
						}
						
						// Catch errors
						catch(\Exception $error) {
						
							// Set that order failed
							$order->update_status("failed", esc_html__("Generating a random secret for this order failed.", "mwc-pay-woocommerce-extension"));
							
							// Throw error
							throw new Exception(esc_html__("Unable to create order.", "woocommerce"));
						}
					
						// Create payment with MWC Pay
						$payment = $this->WC_Gateway_MWC_Pay_mwcPay->createPayment($priceInMwc, $requiredNumberOfBlockConfirmations, $timeout, $this->WC_Gateway_MWC_Pay_getApiUrl([
					
							// Order Id
							"{$this->id}_order_id" => $orderId,
							
							// Secret
							"{$this->id}_secret" => $secret,
							
							// Status
							"{$this->id}_status" => "completed",
							
							// Received timestamp
							"{$this->id}_received_timestamp" => "__received__",
							
							// Completed timestamp
							"{$this->id}_completed_timestamp" => "__completed__"
							
						]), $this->WC_Gateway_MWC_Pay_getApiUrl([
					
							// Order Id
							"{$this->id}_order_id" => $orderId,
							
							// Secret
							"{$this->id}_secret" => $secret,
							
							// Status
							"{$this->id}_status" => "received",
							
							// Kernel commitment
							"{$this->id}_kernel_commitment" => "__kernel_commitment__"
							
						]), $this->WC_Gateway_MWC_Pay_getApiUrl([
					
							// Order Id
							"{$this->id}_order_id" => $orderId,
							
							// Secret
							"{$this->id}_secret" => $secret,
							
							// Status
							"{$this->id}_status" => "confirmed",
							
							// Current number of block confirmations
							"{$this->id}_current_number_of_block_confirmations" => "__confirmations__"
							
						]), $this->WC_Gateway_MWC_Pay_getApiUrl([
					
							// Order Id
							"{$this->id}_order_id" => $orderId,
							
							// Secret
							"{$this->id}_secret" => $secret,
							
							// Status
							"{$this->id}_status" => "expired"
						]));
						
						// Check if creating payment failed
						if($payment === FALSE || $payment === NULL) {
						
							// Set that order failed
							$order->update_status("failed", esc_html__("Creating a payment with your MWC Pay private server failed.", "mwc-pay-woocommerce-extension"));
							
							// Throw error
							throw new Exception(esc_html__("Unable to create order.", "woocommerce"));
						}
						
						// Otherwise
						else {
							
							// Try
							try {
							
								// Don't allow resuming order
								WC()->session->set("order_awaiting_payment", FALSE);
								WC()->session->set("store_api_draft_order", 0);
								WC()->session->save_data();
								
								// Add secret to order
								$order->update_meta_data("_{$this->id}_secret", $secret);
								
								// Add customer ID to order
								$order->update_meta_data("_{$this->id}_customer_id", WC()->session->get_customer_id());
								
								// Check if customer has a session cookie
								$sessionCookie = WC()->session->get_session_cookie();
								if($sessionCookie !== FALSE) {
								
									// Add customer session cookie to order
									$order->update_meta_data("_{$this->id}_customer_session_cookie", implode("||", $sessionCookie));
								}
								
								// Add price in MimbleWimble Coin to order
								$order->update_meta_data("_{$this->id}_price_in_mimblewimble_coin", $priceInMwc);
								
								// Set order's required number of block confirmations
								$order->update_meta_data("_{$this->id}_required_number_of_block_confirmations", (string)$requiredNumberOfBlockConfirmations);
								
								// Set order's current number of block confirmations
								$order->update_meta_data("_{$this->id}_current_number_of_block_confirmations", "0");
								
								// Set order's payment ID
								$order->update_meta_data("_{$this->id}_payment_id", $payment["payment_id"]);
								
								// Set order's recipient payment proof address
								$order->update_meta_data("_{$this->id}_recipient_payment_proof_address", $payment["recipient_payment_proof_address"]);
								
								// Save order
								$order->save();
							}
							
							// Catch errors
							catch(\Exception $error) {
							
								// Set that order failed
								$order->update_status("failed", esc_html__("Saving this order failed.", "mwc-pay-woocommerce-extension"));
								
								// Throw error
								throw new Exception(esc_html__("Unable to create order.", "woocommerce"));
							}
							
							// Return
							return [
							
								// Result
								"result" => "success",
								
								// Redirect
								"redirect" => $this->get_return_url($order),
								
								// URL
								"url" => $this->WC_Gateway_MWC_Pay_getApiUrl([
								
									// Payment URL
									"{$this->id}_payment_url" => $payment["url"]
								]),
								
								// Amount
								"amount" => $priceInMwc,
								
								// Required number of block confirmations
								"required_number_of_block_confirmations" => (string)$requiredNumberOfBlockConfirmations,
								
								// Timeout
								"timeout" => (string)$timeout,
								
								// Recipient payment proof address
								"recipient_payment_proof_address" => $payment["recipient_payment_proof_address"],
								
								// Price in currency
								"price_in_currency" => $order->get_formatted_order_total(),
								
								// Price in MimbleWimble Coin
								"price_in_mimblewimble_coin" => self::WC_Gateway_MWC_Pay_localizeLargeNumber($priceInMwc, TRUE),
								
								// Payment method title
								"payment_method_title" => $this->title,
								
								// Status API
								"status_api" => $this->WC_Gateway_MWC_Pay_getApiUrl([
								
									// Order ID
									"{$this->id}_order_id" => $orderId
									
								], "{$this->id}_get_order_status_nonce"),
								
								// Cancel API
								"cancel_api" => $this->WC_Gateway_MWC_Pay_getApiUrl([
								
									// Order ID
									"{$this->id}_order_id" => $orderId,
									
									// Status
									"{$this->id}_status" => "cancelled"
									
								], "{$this->id}_cancel_order_nonce")
							];
						}
					}
				}
			}
		}
		
		// Add URL settings field
		public function WC_Gateway_MWC_Pay_addUrlSettingsField(string $fieldHtml, string $key, array $data): string {
		
			// Set data to be a URL input
			$data["type"] = "url";
			$data["custom_attributes"]["spellcheck"] = "false";
			$data["custom_attributes"]["autocorrect"] = "off";
			
			// Return text HTML input
			return $this->generate_text_html($key, $data);
		}
		
		// Add number settings field
		public function WC_Gateway_MWC_Pay_addNumberSettingsField(string $fieldHtml, string $key, array $data): string {
		
			// Set data to be a number input
			$data["type"] = "number";
			$data["custom_attributes"]["spellcheck"] = "false";
			$data["custom_attributes"]["autocorrect"] = "off";
			$data["custom_attributes"]["step"] = "any";
			
			// Check if field has a value type
			if(array_key_exists("{$this->id}_value_type", $data) === TRUE) {
			
				// Set result's input mode
				$data["custom_attributes"]["inputmode"] = $data["{$this->id}_value_type"];
			}
			
			// Check if field has a value minimum
			if(array_key_exists("{$this->id}_value_minimum", $data) === TRUE) {
			
				// Set result's minimum
				$data["custom_attributes"]["min"] = $data["{$this->id}_value_minimum"];
			}
			
			// Check if field has a value maximum
			if(array_key_exists("{$this->id}_value_maximum", $data) === TRUE) {
			
				// Set result's maximum
				$data["custom_attributes"]["max"] = $data["{$this->id}_value_maximum"];
			}
			
			// Return text HTML input
			return $this->generate_text_html($key, $data);
		}
		
		// Warn about insecure connection
		public function WC_Gateway_MWC_Pay_warnAboutInsecureConnection(): void {
		
			// Check if enabled, connection isn't secure, and WooCommerce isn't forcing secure checkout pages
			if($this->enabled === "yes" && self::WC_Gateway_MWC_Pay_isConnectionSecure() === FALSE && get_option("woocommerce_force_ssl_checkout") === "no") {
			
				// Display warning
				echo "<div class=\"notice notice-warning is-dismissible\"><p>" . sprintf(esc_html__('Accepting payments with %1$s isn\'t recommended when using an insecure connection. Please serve your entire website over an HTTPS connection or %2$sforce WooCommerce to use secure checkout%3$s.', "mwc-pay-woocommerce-extension"), $this->method_title, "<a href=\"" . esc_url(admin_url("admin.php?page=wc-settings&tab=advanced")) . "\" aria-label=\"" . esc_attr__("Go to WooCommerce advanced settings page", "mwc-pay-woocommerce-extension") . "\">", "</a>") . "</p></div>";
			}
		}
		
		// Process API request
		public function WC_Gateway_MWC_Pay_processApiRequests(): void {
		
			// Check if request method doesn't exists or is invalid
			if(isset($_SERVER) === FALSE || array_key_exists("REQUEST_METHOD", $_SERVER) === FALSE || is_string($_SERVER["REQUEST_METHOD"]) === FALSE) {
			
				// Set no cache headers
				nocache_headers();
				
				// Return internal server error response
				status_header(500);
			}
			
			// Otherwise
			else {
			
				// Check if request is a GET request
				if($_SERVER["REQUEST_METHOD"] === "GET") {
				
					// Check if request is a status request
					if(isset($_GET) === TRUE && array_key_exists("{$this->id}_order_id", $_GET) === TRUE) {
					
						// Process status request
						$this->WC_Gateway_MWC_Pay_processStatusRequest();
					}
					
					// Otherwise
					else {
					
						// Process price request
						$this->WC_Gateway_MWC_Pay_processPriceRequest();
					}
				}
				
				// Otherwise check if request is a POST or OPTIONS request
				else if($_SERVER["REQUEST_METHOD"] === "POST" || $_SERVER["REQUEST_METHOD"] === "OPTIONS") {	
				
					// Process payment request
					$this->WC_Gateway_MWC_Pay_processPaymentRequest();
				}
				
				// Otherwise
				else {
				
					// Set no cache headers
					nocache_headers();
					
					// Return bad request response
					status_header(400);
				}
			}
			
			// Die
			die();
		}
		
		// Add checkout script parameters
		public function WC_Gateway_MWC_Pay_addCheckoutScriptParameters(): void {
		
			// Check if at checkout page and not at an endpoint URL
			if(is_checkout() === TRUE && is_wc_endpoint_url() === FALSE) {
			
				// Check if enabled and currency isn't MimbleWimble Coin
				if($this->enabled === "yes" && get_woocommerce_currency() !== self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID) {
			
					// Get price
					$price = $this->WC_Gateway_MWC_Pay_getPrice();
				
					// Pass parameters to checkout script
					wp_add_inline_script("MwcPayWooCommerceExtension_checkout_script", "const MwcPayWooCommerceExtension_checkout_script_parameters = " . json_encode([
					
						// Enabled
						"enabled" => $this->enabled === "yes",
						
						// Has discount or surcharge
						"has_discount_or_surcharge" => $this->WC_Gateway_MWC_Pay_discountOrSurchargePercent !== "0",
						
						// Price
						"price" => ($price === FAlSE || $price === NULL) ? NULL : $price,
						
						// Get price API
						"get_price_api" => $this->WC_Gateway_MWC_Pay_getApiUrl([], "{$this->id}_get_price_nonce"),
						
						// Currency exchange rate
						"currency_exchange_rate" => (get_woocommerce_currency() === "USD") ? "1" : MwcPayWooCommerceExtension::getCurrencyExchangeRate(get_woocommerce_currency())
						
					]), "before");
				}
				
				// Otherwise
				else {
				
					// Pass parameters to checkout script
					wp_add_inline_script("MwcPayWooCommerceExtension_checkout_script", "const MwcPayWooCommerceExtension_checkout_script_parameters = " . json_encode([
					
						// Enabled
						"enabled" => $this->enabled === "yes",
						
						// Has discount or surcharge
						"has_discount_or_surcharge" => $this->WC_Gateway_MWC_Pay_discountOrSurchargePercent !== "0"
						
					]), "before");
				}
			}
		}
		
		// Show price at checkout
		public function WC_Gateway_MWC_Pay_showPriceAtCheckout(string $value): string {
		
			// Check if enabled and currency isn't MimbleWimble Coin
			if($this->enabled === "yes" && get_woocommerce_currency() !== self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID) {
			
				// Try
				try {
				
					// Get price
					$price = $this->WC_Gateway_MWC_Pay_getPrice();
					
					// Check if getting price was successful
					if($price !== FALSE && $price !== NULL) {
					
						// Get cart's value in USD
						$valueInUsd = Brick\Math\BigDecimal::of(max(WC()->cart->get_total("edit"), 0));
						
						// Check if currency isn't US dollar
						if(get_woocommerce_currency() !== "USD") {
						
							// Check if exchange rate for the currency isn't known
							$exchangeRate = MwcPayWooCommerceExtension::getCurrencyExchangeRate(get_woocommerce_currency());
							if($exchangeRate === NULL) {
							
								// Throw exception
								throw new Exception();
							}
							
							// Check if exchange rate for the currency is negative or zero
							$exchangeRate = Brick\Math\BigDecimal::of($exchangeRate);
							if($exchangeRate->isNegativeOrZero() === TRUE) {
							
								// Throw exception
								throw new Exception();
							}
							
							// Apply exchange rate to the value in USD
							$valueInUsd = $valueInUsd->dividedBy($exchangeRate, wc_get_price_decimals() + $exchangeRate->getScale(), Brick\Math\RoundingMode::UP);
						}
						
						// Get cart's price in MimbleWimble Coin
						$priceInMwc = rtrim(rtrim((string)$valueInUsd->dividedBy($price, self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS, Brick\Math\RoundingMode::UP), "0"), ".");
						
						// Return value with price in MimbleWimble Coin
						return $value . "<span id=\"MwcPayWooCommerceExtension_checkout_total\" class=\"MwcPayWooCommerceExtension_checkout_hide\"> <bdi>(≈&zwj;" . self::WC_Gateway_MWC_Pay_localizeLargeNumber($priceInMwc) . ")</bdi></span>";
					}
				}
				
				// Catch errors
				catch(\Exception $error) {
				
				}
			}
			
			// Return value
			return $value;
		}
		
		// Apply discount or surcharge
		public function WC_Gateway_MWC_Pay_applyDiscountOrSurchange(mixed $cart): void {
		
			// Use WordPress and is Safari
			global $wp, $is_safari;
			
			// Initialize using this payment method
			$usingThisPaymentMethod = FALSE;
			
			// Check if at checkout page and not at an endpoint URL
			if(is_checkout() === TRUE && is_wc_endpoint_url() === FALSE) {
				
				// Check if request isn't using AJAX
				if(wp_doing_ajax() === FALSE && (isset($_SERVER) === FALSE || array_key_exists("HTTP_X_REQUESTED_WITH", $_SERVER) === FALSE || is_string($_SERVER["HTTP_X_REQUESTED_WITH"]) === FALSE || preg_match('/^XMLHttpRequest$/ui', $_SERVER["HTTP_X_REQUESTED_WITH"]) !== 1)) {
				
					// Check if checkout page exists and it's using blocks
					if(wc_get_page_id("checkout") !== -1 && WC_Blocks_Utils::has_block_in_page(wc_get_page_id("checkout"), "woocommerce/checkout") === TRUE) {
					
						// Check if browser isn't Safari
						if(isset($is_safari) === FALSE || $is_safari === FALSE) {
						
							// Check if the first available gateway is this payment method
							$gateways = WC()->payment_gateways()->get_available_payment_gateways();
							if(is_array($gateways) === TRUE && empty($gateways) === FALSE && current($gateways)->id === $this->id) {
							
								// Set using this payment method to true
								$usingThisPaymentMethod = TRUE;
							}
						}
					}
				
					// Otherwise check if choosen payment method in session is this payment method
					else if(isset(WC()->session) === TRUE && WC()->session->get("chosen_payment_method") === $this->id) {
					
						// Set using this payment method to true
						$usingThisPaymentMethod = TRUE;
					}
					
					// Otherwise check if session doesn't exist or doesn't have a chosen payment method
					else if(isset(WC()->session) === FALSE || (isset(WC()->session) === TRUE && WC()->session->get("chosen_payment_method") === NULL)) {
					
						// Check if the first available gateway is this payment method
						$gateways = WC()->payment_gateways()->get_available_payment_gateways();
						if(is_array($gateways) === TRUE && empty($gateways) === FALSE && current($gateways)->id === $this->id) {
						
							// Set using this payment method to true
							$usingThisPaymentMethod = TRUE;
						}
					}
					
					// Otherwise check if session has a chosen payment method
					else if(isset(WC()->session) === TRUE && WC()->session->get("chosen_payment_method") !== NULL) {
					
						// Check if the chosen payment method isn't available and the first available gateway is this payment method
						$gateways = WC()->payment_gateways()->get_available_payment_gateways();
						if(is_array($gateways) === TRUE && empty($gateways) === FALSE && array_key_exists(WC()->session->get("chosen_payment_method"), $gateways) === FALSE && current($gateways)->id === $this->id) {
						
							// Set using this payment method to true
							$usingThisPaymentMethod = TRUE;
						}
					}
				}
				
				// Otherwise
				else {
				
					// Check if request is a POST request
					if(isset($_SERVER) === TRUE && array_key_exists("REQUEST_METHOD", $_SERVER) === TRUE && $_SERVER["REQUEST_METHOD"] === "POST") {
				
						// Check if order is using this payment method
						if(WC()->checkout()->get_posted_data()["payment_method"] === $this->id) {
						
							// Set using this payment method to true
							$usingThisPaymentMethod = TRUE;
						}
					}
				}
			}
			
			// Other check if request is a rest API request
			else if(defined("REST_REQUEST") === TRUE && REST_REQUEST === TRUE && isset($wp) === TRUE && array_key_exists("rest_route", $wp->query_vars) === TRUE && is_string($wp->query_vars["rest_route"]) === TRUE) {
			
				// Check if request is using AJAX
				if(wp_doing_ajax() === TRUE || (isset($_SERVER) === TRUE && array_key_exists("HTTP_X_REQUESTED_WITH", $_SERVER) === TRUE && is_string($_SERVER["HTTP_X_REQUESTED_WITH"]) === TRUE && preg_match('/^XMLHttpRequest$/ui', $_SERVER["HTTP_X_REQUESTED_WITH"]) === 1)) {
				
					// Initialize keep checking
					$keepChecking = TRUE;
					
					// Check if request is a POST request
					if(isset($_SERVER) === TRUE && array_key_exists("REQUEST_METHOD", $_SERVER) === TRUE && $_SERVER["REQUEST_METHOD"] === "POST") {
					
						// Check if request is to the store checkout API
						if(preg_match('/^\/wc\/store\/(?:v\d+\/)?checkout$/u', untrailingslashit($wp->query_vars["rest_route"])) === 1) {
						
							// Set keep checking to false
							$keepChecking = FALSE;
							
							// Create request
							$request = new WP_REST_Request($_SERVER["REQUEST_METHOD"], untrailingslashit($wp->query_vars["rest_route"]));
							$request->set_headers((new WP_REST_Server())->get_headers(wp_unslash($_SERVER)));
							$request->set_body(WP_REST_Server::get_raw_data());
							
							// Check if request's payment method is this payment method
							if(wc_clean(wp_unslash($request["payment_method"] ?? "")) === $this->id) {
								
								// Set using this payment method to true
								$usingThisPaymentMethod = TRUE;
							}
						}
					}
					
					// Check if keep checking
					if($keepChecking === TRUE) {
					
						// Check if request is to the store API and is using this payment method
						if(preg_match('/^\/wc\/store\//u', $wp->query_vars["rest_route"]) === 1 && isset($_GET) === TRUE && array_key_exists("{$this->id}_payment_method", $_GET) === TRUE && $_GET["{$this->id}_payment_method"] === $this->id) {
						
							// Set using this payment method to true
							$usingThisPaymentMethod = TRUE;
						}
					}
				}
			}
			
			// Check if using this payment method
			if($usingThisPaymentMethod === TRUE) {
			
				// Check if discount or surcharge percent isn't zero
				$discountOrSurchargePercent = Brick\Math\BigDecimal::of($this->WC_Gateway_MWC_Pay_discountOrSurchargePercent);
				
				if($discountOrSurchargePercent->isZero() === FALSE) {
				
					// Get percent change
					$percentChange = $discountOrSurchargePercent->exactlyDividedBy(100)->multipliedBy(max($cart->get_cart_contents_total(), 0))->toScale(wc_get_price_decimals(), Brick\Math\RoundingMode::DOWN)->toFloat();
					
					// Check if a surcharge is being applied
					if($discountOrSurchargePercent->isPositive() === TRUE) {
					
						// Apply surcharge to cart
						$cart->add_fee(esc_html(sprintf(__('%1$s (%2$s%% surcharge)', "mwc-pay-woocommerce-extension"), $this->title, preg_replace('/\./u', wc_get_price_decimal_separator(), (string)$discountOrSurchargePercent->abs(), 1))), $percentChange);
					}
					
					// Otherwise
					else {
					
						// Apply discount to cart
						$cart->add_fee(esc_html(sprintf(__('%1$s (%2$s%% discount)', "mwc-pay-woocommerce-extension"), $this->title, preg_replace('/\./u', wc_get_price_decimal_separator(), (string)$discountOrSurchargePercent->abs(), 1))), $percentChange);
					}
				}
			}
		}
		
		// Apply display format to title
		public function WC_Gateway_MWC_Pay_applyDisplayFormatToTitle(string $title, string $gatewayId): string {
		
			// Check if gateway is this gateway, at the checkout page and not at an endpoint URL, and display format doesn't include a title
			if($gatewayId === $this->id && is_checkout() === TRUE && is_wc_endpoint_url() === FALSE && $this->WC_Gateway_MWC_Pay_displayFormat !== "title" && $this->WC_Gateway_MWC_Pay_displayFormat !== "title_and_icon") {
			
				// Return nothing
				return "";
			}
			
			// Otherwise
			else {
			
				// Return title
				return $title;
			}
		}
		
		// Apply display format to icon
		public function WC_Gateway_MWC_Pay_applyDisplayFormatToIcon(string $icon, string $gatewayId): string {
		
			// Check if gateway is this gateway and at the checkout page and not at an endpoint URL
			if($gatewayId === $this->id && is_checkout() === TRUE && is_wc_endpoint_url() === FALSE) {
			
				// Check if display format doesn't include an icon
				if($this->WC_Gateway_MWC_Pay_displayFormat !== "icon" && $this->WC_Gateway_MWC_Pay_displayFormat !== "title_and_icon") {
			
					// Return nothing
					return "";
				}
				
				// Otherwise
				else {
				
					// Return icon
					return "<img " . (($this->WC_Gateway_MWC_Pay_displayFormat === "icon") ? "style=\"margin-left: 0;\"" : "style=\"max-height: 24px;\"") . " src=\"" . esc_url($this->icon) . "\" alt=\"" . esc_attr($this->title) . "\">";
				}
			}
			
			// Otherwise
			else {
			
				// Return icon
				return $icon;
			}
		}
		
		// Process status request
		private function WC_Gateway_MWC_Pay_processStatusRequest(): void {
		
			// Set no cache headers
			nocache_headers();
			
			// Check if request is to set an order's status
			if(isset($_GET) === TRUE && array_key_exists("{$this->id}_order_id", $_GET) === TRUE && array_key_exists("{$this->id}_status", $_GET) === TRUE) {
			
				// Check if parameters are invalid
				if(is_string($_GET["{$this->id}_order_id"]) === FALSE || $_GET["{$this->id}_order_id"] === "" || is_string($_GET["{$this->id}_status"]) === FALSE) {
				
					// Return bad request response
					status_header(400);
				}
				
				// Otherwise
				else {
			
					// Get order
					$order = wc_get_order($_GET["{$this->id}_order_id"]);
					
					// Check if order doesn't exist or didn't use this payment method
					if($order === FALSE || $order->has_status("trash") === TRUE || $order->get_payment_method() !== $this->id) {
					
						// Check if status isn't expired or completed
						if($_GET["{$this->id}_status"] !== "expired" && $_GET["{$this->id}_status"] !== "completed") {
						
							// Return bad request response
							status_header(400);
						}
					}
					
					// Otherwise
					else {
					
						// Check status
						switch($_GET["{$this->id}_status"]) {
						
							// Cancelled
							case "cancelled":
							
								// Get order's customer ID
								$customerId = $order->get_meta("_{$this->id}_customer_id");
								
								// Check if order doesn't have a customer ID or order isn't for the customer
								if($customerId === "" || isset(WC()->session) === FALSE || hash_equals($customerId, WC()->session->get_customer_id()) === FALSE) {
								
									// Return forbidden response
									status_header(403);
								}
								
								// Otherwise check if nonce is invalid
								else if(array_key_exists(self::WC_Gateway_MWC_Pay_NONCE_NAME, $_GET) === FALSE || is_string($_GET[self::WC_Gateway_MWC_Pay_NONCE_NAME]) === FALSE || wp_verify_nonce($_GET[self::WC_Gateway_MWC_Pay_NONCE_NAME], "{$this->id}_cancel_order_nonce") === FALSE) {
								
									// Return forbidden response
									status_header(403);
								}
								
								// Otherwise check if order isn't pending payment
								else if($order->has_status("pending") === FALSE) {
								
									// Return bad request response
									status_header(400);
								}
								
								// Otherwise
								else {
								
									// Try
									try {
									
										// Remove order's recipient payment proof address
										$order->delete_meta_data("_{$this->id}_recipient_payment_proof_address");
										
										// Set that order is cancelled
										$order->set_status("cancelled", esc_html__("Order cancelled by customer.", "woocommerce"));
										
										// Save order
										$order->save();
									}
									
									// Catch errors
									catch(\Exception $error) {
									
										// Return internal server error response
										status_header(500);
									}
								}
								
								// Break
								break;
							
							// Expired
							case "expired":
							
								// Check if parameters are invalid
								if(array_key_exists("{$this->id}_secret", $_GET) === FALSE || is_string($_GET["{$this->id}_secret"]) === FALSE) {
								
									// Return bad request response
									status_header(400);
								}
								
								// Otherwise
								else {
							
									// Get order's secret
									$secret = $order->get_meta("_{$this->id}_secret");
									
									// Check if order has a secret and it matches the provided secret
									if($secret !== "" && hash_equals($secret, $_GET["{$this->id}_secret"]) === TRUE) {
									
										// Check if order is pending payment
										if($order->has_status("pending") === TRUE) {
										
											// Try
											try {
											
												// Set that order is expired
												$order->update_meta_data("_{$this->id}_is_expired", "true");
												
												// Remove order's recipient payment proof address
												$order->delete_meta_data("_{$this->id}_recipient_payment_proof_address");
												
												// Set that order is cancelled
												$order->set_status("cancelled", esc_html__("Unpaid order cancelled - time limit reached.", "woocommerce"));
												
												// Save order
												$order->save();
											}
											
											// Catch errors
											catch(\Exception $error) {
											
												// Return internal server error response
												status_header(500);
											}
										}
									}
								}
								
								// Break
								break;
							
							// Received
							case "received":
							
								// Check if parameters are invalid
								if(array_key_exists("{$this->id}_secret", $_GET) === FALSE || is_string($_GET["{$this->id}_secret"]) === FALSE || array_key_exists("{$this->id}_kernel_commitment", $_GET) === FALSE || is_string($_GET["{$this->id}_kernel_commitment"]) === FALSE || $_GET["{$this->id}_kernel_commitment"] === "") {
								
									// Return bad request response
									status_header(400);
								}
								
								// Otherwise
								else {
							
									// Get order's secret
									$secret = $order->get_meta("_{$this->id}_secret");
									
									// Check if order doesn't have a secret or the provided secret isn't correct
									if($secret === "" || hash_equals($secret, $_GET["{$this->id}_secret"]) === FALSE) {
									
										// Return bad request response
										status_header(400);
									}
									
									// Otherwise check if order is pending payment
									else if($order->has_status("pending") === TRUE) {
									
										// Try
										try {
										
											// Set that order is paid
											$order->update_meta_data("_{$this->id}_is_paid", "true");
											
											// Set order's kernel commitment
											$order->update_meta_data("_{$this->id}_kernel_commitment", $_GET["{$this->id}_kernel_commitment"]);
											
											// Set that order is on-hold
											$order->set_status("on-hold", esc_html__("Payment was received for this order, but it hasn't been confirmed on-chain yet. This order will start processing once its payment achieves enough block confirmations.", "mwc-pay-woocommerce-extension"));
											
											// Save order
											$order->save();
										}
										
										// Catch errors
										catch(\Exception $error) {
										
											// Return internal server error response
											status_header(500);
											
											// Return
											return;
										}
									
										// Check if order's has a customer session cookie
										$sessionCookie = $order->get_meta("_{$this->id}_customer_session_cookie");
										if($sessionCookie !== "") {
										
											// Try
											try {
												// Get customer's cart from their session cookie
												$cart = new WC_Cart_MWC_Pay($sessionCookie);
												
												// Check if customer's cart hasn't changed since placing the order
												if($order->has_cart_hash($cart->get_cart_hash()) === TRUE) {
												
													// Empty cart
													$cart->empty_cart();
													WC()->session->save_data();
												}
											}
											
											// Catch errors
											catch(\Exception $error) {
											
											}
										}
									}
									
									// Otherwise check if order is on-hold
									else if($order->has_status("on-hold") === TRUE) {
									
										// Try
										try {
										
											// Set order's kernel commitment
											$order->update_meta_data("_{$this->id}_kernel_commitment", $_GET["{$this->id}_kernel_commitment"]);
											
											// Save order
											$order->save();
										}
										
										// Catch errors
										catch(\Exception $error) {
										
											// Return internal server error response
											status_header(500);
										}
									}
									
									// Otherwise
									else {
									
										// Return bad request response
										status_header(400);
									}
								}
								
								// Break
								break;
								
							// Confirmed
							case "confirmed":
							
								// Check if parameters are invalid
								if(array_key_exists("{$this->id}_secret", $_GET) === FALSE || is_string($_GET["{$this->id}_secret"]) === FALSE || array_key_exists("{$this->id}_current_number_of_block_confirmations", $_GET) === FALSE || is_string($_GET["{$this->id}_current_number_of_block_confirmations"]) === FALSE || preg_match('/^(?:0|[1-9]\d*)$/u', $_GET["{$this->id}_current_number_of_block_confirmations"]) !== 1 || Brick\Math\BigInteger::of($_GET["{$this->id}_current_number_of_block_confirmations"])->isGreaterThan(self::WC_Gateway_MWC_Pay_UINT32_MAX) === TRUE) {
								
									// Return bad request response
									status_header(400);
								}
								
								// Otherwise
								else {
							
									// Get order's secret
									$secret = $order->get_meta("_{$this->id}_secret");
									
									// Check if order doesn't have a secret or the provided secret isn't correct
									if($secret === "" || hash_equals($secret, $_GET["{$this->id}_secret"]) === FALSE) {
									
										// Return bad request response
										status_header(400);
									}
									
									// Otherwise check if order isn't on-hold
									else if($order->has_status("on-hold") === FALSE) {
									
										// Return bad request response
										status_header(400);
									}
									
									// Otherwise
									else {
									
										// Try
										try {
										
											// Set order's current number of block confirmations
											$order->update_meta_data("_{$this->id}_current_number_of_block_confirmations", $_GET["{$this->id}_current_number_of_block_confirmations"]);
											
											// Save order
											$order->save();
										}
										
										// Catch errors
										catch(\Exception $error) {
										
											// Return internal server error response
											status_header(500);
										}
									}
								}
								
								// Break
								break;
								
							// Completed
							case "completed":
							
								// Check if parameters are invalid
								if(array_key_exists("{$this->id}_secret", $_GET) === FALSE || is_string($_GET["{$this->id}_secret"]) === FALSE || array_key_exists("{$this->id}_received_timestamp", $_GET) === FALSE || is_string($_GET["{$this->id}_received_timestamp"]) === FALSE || preg_match('/^(?:0|[1-9]\d*)$/u', $_GET["{$this->id}_received_timestamp"]) !== 1 || array_key_exists("{$this->id}_completed_timestamp", $_GET) === FALSE || is_string($_GET["{$this->id}_completed_timestamp"]) === FALSE || preg_match('/^(?:0|[1-9]\d*)$/u', $_GET["{$this->id}_completed_timestamp"]) !== 1) {
								
									// Return bad request response
									status_header(400);
								}
								
								// Otherwise
								else {
							
									// Get order's secret
									$secret = $order->get_meta("_{$this->id}_secret");
									
									// Check if order has a secret and it matches the provided secret
									if($secret !== "" && hash_equals($secret, $_GET["{$this->id}_secret"]) === TRUE) {
									
										// Check if order is on-hold
										if($order->has_status("on-hold") === TRUE) {
										
											// Set that order is completed
											$order->update_meta_data("_{$this->id}_is_completed", "true");
											
											// Check if order has a required number of block confirmations
											if($order->get_meta("_{$this->id}_required_number_of_block_confirmations") !== "") {
											
												// Set order's current number of block confirmations to its required number of block confirmations
												$order->update_meta_data("_{$this->id}_current_number_of_block_confirmations", $order->get_meta("_{$this->id}_required_number_of_block_confirmations"));
											}
											
											// Check if order's payment's time from being received to time to being completed exceeds the maximum allowed time for that
											if(Brick\Math\BigInteger::of($_GET["{$this->id}_completed_timestamp"])->minus($_GET["{$this->id}_received_timestamp"])->isGreaterThan($this->WC_Gateway_MWC_Pay_completedPaymentMaximumAllowedTimeSinceBeingReceived) === TRUE) {
										
												// Try
												try {
												
													// Set that order can be processed
													$order->update_meta_data("_{$this->id}_can_be_processed", "true");
													
													// Set that the order failed
													$order->set_status("failed", esc_html__("Order's payment has achieved enough block confirmations but not within the allowed amount of time. The customer either nefariously delayed broadcasting the order's payment or the MimbleWimble Coin network is congested. You can choose to either refund or process this order.", "mwc-pay-woocommerce-extension"));
													
													// Save order
													$order->save();
												}
												
												// Catch errors
												catch(\Exception $error) {
												
													// Return internal server error response
													status_header(500);
												}
											}
										
											// Otherwise check if setting that order is complete failed
											else if($order->payment_complete() === FALSE) {
												
												// Return internal server error response
												status_header(500);
											}
											
											// Otherwise
											else {
											
												// Add note to order
												$order->add_order_note(esc_html__("Order's payment has achieved enough block confirmations.", "mwc-pay-woocommerce-extension"));
											}
										}
									}
								}
								
								// Break
								break;
							
							// Default
							default:
							
								// Return bad request response
								status_header(400);
								
								// Break
								break;
						}
					}
				}
			}
			
			// Otherwise check if request is to get an order's status
			else if(isset($_GET) === TRUE && array_key_exists("{$this->id}_order_id", $_GET) === TRUE) {
			
				// Check if parameters are invalid
				if(is_string($_GET["{$this->id}_order_id"]) === FALSE || $_GET["{$this->id}_order_id"] === "") {
				
					// Return bad request response
					status_header(400);
				}
				
				// Otherwise
				else {
				
					// Get order
					$order = wc_get_order($_GET["{$this->id}_order_id"]);
					
					// Check if order doesn't exist or didn't use this payment method
					if($order === FALSE || $order->get_payment_method() !== $this->id) {
					
						// Return bad request response
						status_header(400);
					}
					
					// Otherwise
					else {
					
						// Get order's customer ID
						$customerId = $order->get_meta("_{$this->id}_customer_id");
						
						// Check if order doesn't have a customer ID or order isn't for the customer
						if($customerId === "" || isset(WC()->session) === FALSE || hash_equals($customerId, WC()->session->get_customer_id()) === FALSE) {
						
							// Return forbidden response
							status_header(403);
						}
						
						// Otherwise check if nonce is invalid
						else if(array_key_exists(self::WC_Gateway_MWC_Pay_NONCE_NAME, $_GET) === FALSE || is_string($_GET[self::WC_Gateway_MWC_Pay_NONCE_NAME]) === FALSE || wp_verify_nonce($_GET[self::WC_Gateway_MWC_Pay_NONCE_NAME], "{$this->id}_get_order_status_nonce") === FALSE) {
						
							// Return forbidden response
							status_header(403);
						}
						
						// Otherwise
						else {
						
							// Return order status
							echo json_encode([
							
								// Paid
								"paid" => $order->has_status(["failed", "refunded", "cancelled", "trash"]) === FALSE && $order->get_meta("_{$this->id}_is_paid") !== "",
								
								// Expired
								"expired" => $order->has_status("cancelled") === TRUE && $order->get_meta("_{$this->id}_is_expired") !== "",
								
								// Cancelled
								"cancelled" => ($order->has_status("cancelled") === TRUE && $order->get_meta("_{$this->id}_is_expired") === "") || $order->has_status(["failed", "refunded", "trash"]) === TRUE
							]);
						}
					}
				}
			}
			
			// Otherwise
			else {
			
				// Return bad request response
				status_header(400);
			}
		}
		
		// Process price request
		private function WC_Gateway_MWC_Pay_processPriceRequest(): void {
		
			// Set no cache headers
			nocache_headers();
			
			// Check if nonce is invalid
			if(isset($_GET) === FALSE || array_key_exists(self::WC_Gateway_MWC_Pay_NONCE_NAME, $_GET) === FALSE || is_string($_GET[self::WC_Gateway_MWC_Pay_NONCE_NAME]) === FALSE || wp_verify_nonce($_GET[self::WC_Gateway_MWC_Pay_NONCE_NAME], "{$this->id}_get_price_nonce") === FALSE) {
			
				// Return forbidden response
				status_header(403);
			}
			
			// Otherwise check if not enabled or currency is MimbleWimble Coin
			else if($this->enabled !== "yes" || get_woocommerce_currency() === self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID) {
			
				// Return forbidden response
				status_header(403);
			}
			
			// Otherwise
			else {
				
				// Get price
				$price = $this->WC_Gateway_MWC_Pay_getPrice();
			
				// Return price
				echo json_encode([
				
					// Price
					"price" => ($price === FAlSE || $price === NULL) ? NULL : $price,
					
					// Currency exchange rate
					"currency_exchange_rate" => (get_woocommerce_currency() === "USD") ? "1" : MwcPayWooCommerceExtension::getCurrencyExchangeRate(get_woocommerce_currency())
				]);
			}
		}
		
		// Process payment request
		private function WC_Gateway_MWC_Pay_processPaymentRequest(): void {
		
			// Check if parameters are invalid
			if(isset($_GET) === FALSE || array_key_exists("{$this->id}_payment_url", $_GET) === FALSE || is_string($_GET["{$this->id}_payment_url"]) === FALSE || preg_match('/^[a-z0-9]+\/v2\/foreign$/ui', $_GET["{$this->id}_payment_url"]) !== 1) {
			
				// Set no cache headers
				nocache_headers();
				
				// Return bad request response
				status_header(400);
			}
			
			// Otherwise
			else {
			
				// Get MWC Pay's public server info
				$publicServerInfo = $this->WC_Gateway_MWC_Pay_mwcPay->getPublicServerInfo();
				
				// Check if getting public server info failed
				if($publicServerInfo === FALSE || $publicServerInfo === NULL) {
				
					// Set no cache headers
					nocache_headers();
					
					// Return internal server error response
					status_header(500);
				}
				
				// Otherwise
				else {
				
					// Check if getting headers failed
					$headers = getallheaders();
					if($headers === FALSE) {
					
						// Set no cache headers
						nocache_headers();
						
						// Return internal server error response
						status_header(500);
					}
					
					// Otherwise
					else {
					
						// Check if getting body failed
						$body = file_get_contents("php://input");
						if($body === FALSE) {
						
							// Set no cache headers
							nocache_headers();
							
							// Return internal server error response
							status_header(500);
						}
						
						// Otherwise
						else {
						
							// Remove all headers except content type
							$headers = array_filter($headers, function(string $key): bool {
								
								// Return if header is content type
								return preg_match('/^Content-Type$/ui', $key) === 1;
									
							}, ARRAY_FILTER_USE_KEY);
							
							// Set accept encoding header
							$headers["Accept-Encoding"] = "identity";
							
							// Send payment request to MWC Pay's public server
							$response = wp_remote_request("{$publicServerInfo["url"]}/{$_GET["{$this->id}_payment_url"]}", [
							
								// Method
								"method" => $_SERVER["REQUEST_METHOD"],
								
								// Headers
								"headers" => $headers,
								
								// User agent
								"user-agent" => "WordPress/" . get_bloginfo("version"),
								
								// Body
								"body" => $body,
								
								// Timeout
								"timeout" => self::WC_Gateway_MWC_Pay_PAYMENT_REQUEST_TIMEOUT_SECONDS
							]);
							
							// Check if performing request failed
							if(wp_remote_retrieve_response_code($response) === "") {
							
								// Set no cache headers
								nocache_headers();
								
								// Return internal server error response
								status_header(500);
							}
							
							// Otherwise
							else {
							
								// Go through all received headers
								foreach(wp_remote_retrieve_headers($response) as $key => $value) {
								
									// Set response header
									header("$key: $value");
								}
								
								// Return received status code
								status_header(wp_remote_retrieve_response_code($response));
								
								// Return received body
								echo wp_remote_retrieve_body($response);
							}
						}
					}
				}
			}
		}
		
		// Get API URL
		private function WC_Gateway_MWC_Pay_getApiUrl(array $parameters, ?string $nonce = NULL): string {
		
			// Get API URL
			$apiUrl = WC()->api_request_url($this->id);
			
			// Check if adding a nonce
			if($nonce !== NULL) {
			
				// Add nonce to parameters
				$parameters[self::WC_Gateway_MWC_Pay_NONCE_NAME] = wp_create_nonce($nonce);
			}
			
			// Return API URL with parameters
			return $apiUrl . ((preg_match('/\?/u', $apiUrl) !== 1) ? "?" : "&") . http_build_query($parameters);
		}
		
		// Get price
		private function WC_Gateway_MWC_Pay_getPrice(): string | FALSE | NULL {
		
			// Initialize price
			static $price = "";
			
			// Check if price hasn't been obtained
			if($price === "") {
			
				// Get price from MWC Pay
				$price = $this->WC_Gateway_MWC_Pay_mwcPay->getPrice();
				
				// Check if getting price was successful and price is negative or zero
				if($price !== FALSE && $price !== NULL && Brick\Math\BigDecimal::of($price)->isNegativeOrZero() === TRUE) {
				
					// Set price to false
					$price = FALSE;
				}
			}
			
			// Return price
			return $price;
		}
		
		// Add base script
		public static function WC_Gateway_MWC_Pay_addBaseScript(): void {
		
			// Add base script
			wp_register_script("MwcPayWooCommerceExtension_base_script", FALSE);
			
			// Pass parameters to base script
			wp_add_inline_script("MwcPayWooCommerceExtension_base_script", "const MwcPayWooCommerceExtension_base_script_parameters = " . json_encode([
			
				// Gateway ID
				"gateway_id" => self::WC_Gateway_MWC_Pay_ID,
				
				// Currency ID
				"currency_id" => self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID,
				
				// Currency symbol
				"currency_symbol" => self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_SYMBOL,
				
				// Currency decimals
				"currency_decimals" => self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS,
				
				// URL protocol
				"url_protocol" => self::WC_Gateway_MWC_Pay_URL_PROTOCOL
			]));
		}
		
		// Add settings link
		public static function WC_Gateway_MWC_Pay_addSettingsLink(array $actions): array {
		
			// Create settings link
			$settingsLink = ["settings" => "<a href=\"" . esc_url(admin_url("admin.php?page=wc-settings&tab=checkout&section=mwc_pay")) . "\" aria-label=\"" . esc_attr__("Go to MWC Pay WooCommerce Extension settings page", "mwc-pay-woocommerce-extension") . "\">" . esc_html__("Settings", "woocommerce") . "</a>"];
			
			// Add settings link to actions and return it
			return array_merge($settingsLink, $actions);
		}
		
		// Add checkout scripts and styles
		public static function WC_Gateway_MWC_Pay_addCheckoutScriptsAndStyles(array $pluginData): void {
			
			// Check if at checkout page and not at an endpoint URL
			if(is_checkout() === TRUE && is_wc_endpoint_url() === FALSE) {
			
				// Add checkout scripts and styles
				wp_enqueue_script("MwcPayWooCommerceExtension_bignumber-js_script", plugins_url("../assets/js/bignumber.js-9.1.1.min.js", __FILE__), [], $pluginData["Version"], TRUE);
				wp_enqueue_script("MwcPayWooCommerceExtension_qrcode-generator_script", plugins_url("../assets/js/qrcode-generator-1.4.4.min.js", __FILE__), [], $pluginData["Version"], TRUE);
				wp_enqueue_script("MwcPayWooCommerceExtension_checkout_script", plugins_url("../assets/js/checkout.min.js", __FILE__), ["wp-api-fetch", "wp-data", "wp-escape-html", "wp-i18n", "wc-blocks-components", "wc-blocks-data-store", "wc-price-format", "react", "jquery", "MwcPayWooCommerceExtension_base_script", "MwcPayWooCommerceExtension_bignumber-js_script", "MwcPayWooCommerceExtension_qrcode-generator_script"], $pluginData["Version"], TRUE);
				wp_enqueue_style("MwcPayWooCommerceExtension_checkout_style", plugins_url("../assets/css/checkout.min.css", __FILE__), [], $pluginData["Version"]);
				
				// Load translations for checkout scripts
				wp_set_script_translations("MwcPayWooCommerceExtension_checkout_script", $pluginData["TextDomain"], plugin_dir_path(__FILE__) . "..{$pluginData["DomainPath"]}");
			}
		}
		
		// Add checkout content
		public static function WC_Gateway_MWC_Pay_addCheckoutContent(): void {
		
			// Check if at checkout page and not at an endpoint URL
			if(is_checkout() === TRUE && is_wc_endpoint_url() === FALSE) {
			
				// Include checkout content
				require_once plugin_dir_path(__FILE__) . "../includes/checkout.php";
			}
		}
		
		// Add payment gateway
		public static function WC_Gateway_MWC_Pay_addPaymentGateway(array $methods): array {
		
			// Add gateway to methods
			$methods[] = "WC_Gateway_MWC_Pay";
			
			// Return methods
			return $methods;
		}
		
		// Add payment blocks
		public static function WC_Gateway_MWC_Pay_addPaymentBlocks(array $pluginData): void {
		
			// Set gateway ID
			$gatewayId = self::WC_Gateway_MWC_Pay_ID;
			
			// Include MWC Pay blocks
			require_once plugin_dir_path(__FILE__) . "../includes/mwc_pay_blocks.php";
		}
		
		// Remove pay and cancel order buttons
		public static function WC_Gateway_MWC_Pay_removePayAndCancelOrderButtons(array $actions, mixed $order): array {
		
			// Check if order used this payment method
			if($order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID) {
			
				// Remove pay action
				unset($actions["pay"]);
				
				// Check if order isn't pending payment
				if($order->has_status("pending") === FALSE) {
				
					// Remove cancel action
					unset($actions["cancel"]);
				}
			}
			
			// Return actions
			return $actions;
		}
		
		// Limit cancelling orders
		public static function WC_Gateway_MWC_Pay_limitCancellingOrders(array $statuses, mixed $order): array {
		
			// Check if order used this payment method
			if($order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID) {
			
				// Return pending payment status
				return ["pending"];
			}
			
			// Otherwise
			else {
			
				// Return statuses
				return $statuses;
			}
		}
		
		// Limit changing individual order status
		public static function WC_Gateway_MWC_Pay_limitChangingIndividualOrderStatus(array $orderStatuses): array {
		
			// Use post and action
			global $post, $action;
			
			// Check if editing an order
			if((isset($_GET) === TRUE && array_key_exists("page", $_GET) === TRUE && $_GET["page"] === "wc-orders" && array_key_exists("action", $_GET) === TRUE && $_GET["action"] === "edit" && array_key_exists("id", $_GET) === TRUE && is_string($_GET["id"]) === TRUE && $_GET["id"] !== "") || (isset($post) === TRUE && $post->post_type === "shop_order" && isset($action) === TRUE && $action === "edit")) {
			
				// Get order
				$order = wc_get_order((isset($_GET) === TRUE && array_key_exists("page", $_GET) === TRUE && $_GET["page"] === "wc-orders" && array_key_exists("action", $_GET) === TRUE && $_GET["action"] === "edit" && array_key_exists("id", $_GET) === TRUE && is_string($_GET["id"]) === TRUE && $_GET["id"] !== "") ? $_GET["id"] : $post->ID);
				
				// Check if order exists and used this payment method
				if($order !== FALSE && $order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID) {
				
					// Check if order is pending payment
					if($order->has_status("pending") === TRUE) {
					
						// Set valid statuses
						$validStatuses = ["wc-pending", "wc-cancelled"];
					}
					
					// Otherwise check if order is processing
					else if($order->has_status("processing") === TRUE) {
					
						// Set valid statuses
						$validStatuses = ["wc-processing", "wc-completed", "wc-cancelled"];
					}
					
					// Otherwise check if order is on-hold
					else if($order->has_status("on-hold") === TRUE) {
					
						// Set valid statuses
						$validStatuses = ["wc-on-hold", "wc-cancelled"];
					}
					
					// Otherwise check if order is completed
					else if($order->has_status("completed") === TRUE) {
					
						// Set valid statuses
						$validStatuses = ["wc-completed", "wc-cancelled"];
					}
					
					// Otherwise check if order is cancelled
					else if($order->has_status("cancelled") === TRUE) {
					
						// Set valid statuses
						$validStatuses = ["wc-cancelled"];
					}
					
					// Otherwise check if order is refunded
					else if($order->has_status("refunded") === TRUE) {
					
						// Set valid statuses
						$validStatuses = ["wc-refunded"];
					}
					
					// Otherwise check if order is failed
					else if($order->has_status("failed") === TRUE) {
					
						// Check if order can be processed
						if($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_can_be_processed") !== "") {
						
							// Set valid statuses
							$validStatuses = ["wc-failed", "wc-processing", "wc-completed"];
						}
						
						// Otherwise
						else {
						
							// Set valid statuses
							$validStatuses = ["wc-failed"];
						}
					}
					
					// Otherwise
					else {
					
						// Set valid statuses
						$validStatuses = ["wc-pending", "wc-processing", "wc-on-hold", "wc-completed", "wc-cancelled", "wc-refunded", "wc-failed", "wc-checkout-draft"];
					}
					
					// Return filtering order statuses
					return array_filter($orderStatuses, function(string $key) use ($validStatuses): bool {
					
						// Return if order status is a valid status for the order
						return in_array($key, $validStatuses) === TRUE;
						
					}, ARRAY_FILTER_USE_KEY);
				}
			}
			
			// Return order statuses
			return $orderStatuses;
		}
		
		// Limit changing bulk order status
		public static function WC_Gateway_MWC_Pay_limitChangingBulkOrderStatus(array $actions): array {
		
			// Set valid actions
			$validActions = ["trash", "untrash", "delete"];
			
			// Return filtering actions
			return array_filter($actions, function(string $key) use ($validActions): bool {
			
				// Return if action is valid
				return in_array($key, $validActions) === TRUE;
				
			}, ARRAY_FILTER_USE_KEY);
		}
		
		// Limit refunding orders
		public static function WC_Gateway_MWC_Pay_limitRefundingOrders(bool $refund, int $orderId, mixed $order): bool {
		
			// Check if order used this payment method
			if($order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID) {
			
				// Return if can show and order is completed and not refunded
				return $refund === TRUE && $order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_is_completed") !== "" && $order->has_status("refunded") === FALSE;
			}
			
			// Otherwise
			else {
			
				// Return refund
				return $refund;
			}
		}
		
		// Add partially refunded order note
		public static function WC_Gateway_MWC_Pay_addPartiallyRefundedOrderNote(int $orderId, int $refundId): void {
		
			// Get order and refund
			$order = wc_get_order($orderId);
			$refund = wc_get_order($refundId);
			
			// Check if order and refund exist and the order used this payment method
			if($order !== FALSE && $refund !== FALSE && $order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID) {
			
				// Check if order doesn't have a price in MimbleWimble Coin or order didn't require a payment
				if($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_price_in_mimblewimble_coin") === "" || $order->get_total() <= 0) {
				
					// Add note to order
					$order->add_order_note(esc_html__("Order was partially refunded. Make sure you manually communicate with the customer to refund some of their MimbleWimble Coin.", "mwc-pay-woocommerce-extension"));
				}
				
				// Otherwise
				else {
				
					// Get amount of MimbleWimble Coin refunded
					$amountOfMimbleWimbleCoinRefunded = rtrim(rtrim((string)Brick\Math\BigDecimal::of($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_price_in_mimblewimble_coin"))->multipliedBy($refund->get_amount())->dividedBy($order->get_total(), self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS, Brick\Math\RoundingMode::DOWN), "0"), ".");
					
					// Add note to order
					$order->add_order_note(sprintf(esc_html__("Order was partially refunded. Make sure you manually communicate with the customer to refund their %s.", "mwc-pay-woocommerce-extension"), "<bdi>" . self::WC_Gateway_MWC_Pay_localizeLargeNumber($amountOfMimbleWimbleCoinRefunded) . "</bdi>"));
				}
			}
		}
		
		// Add fully refunded order note
		public static function WC_Gateway_MWC_Pay_addFullyRefundedOrderNote(int $orderId, int $refundId): void {
		
			// Get order and refund
			$order = wc_get_order($orderId);
			$refund = wc_get_order($refundId);
			
			// Check if order and refund exist and the order used this payment method
			if($order !== FALSE && $refund !== FALSE && $order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID) {
			
				// Check if order doesn't have a price in MimbleWimble Coin or order didn't require a payment
				if($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_price_in_mimblewimble_coin") === "" || $order->get_total() <= 0) {
				
					// Set that order was refunded
					$order->update_status("refunded", esc_html__("Order was fully refunded. Make sure you manually communicate with the customer to refund all of their MimbleWimble Coin.", "mwc-pay-woocommerce-extension"));
				}
				
				// Otherwise
				else {
				
					// Get amount of MimbleWimble Coin refunded
					$amountOfMimbleWimbleCoinRefunded = rtrim(rtrim((string)Brick\Math\BigDecimal::of($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_price_in_mimblewimble_coin"))->minus(Brick\Math\BigDecimal::of($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_price_in_mimblewimble_coin"))->multipliedBy(Brick\Math\BigDecimal::of($order->get_total_refunded())->minus($refund->get_amount()))->dividedBy($order->get_total(), self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS, Brick\Math\RoundingMode::DOWN)), "0"), ".");
				
					// Set that order was refunded
					$order->update_status("refunded", sprintf(esc_html__("Order was fully refunded. Make sure you manually communicate with the customer to refund their %s.", "mwc-pay-woocommerce-extension"), "<bdi>" . self::WC_Gateway_MWC_Pay_localizeLargeNumber($amountOfMimbleWimbleCoinRefunded) . "</bdi>"));
				}
			}
		}
		
		// Prevent automatically setting fully refunded order status
		public static function WC_Gateway_MWC_Pay_preventAutomaticallySettingFullyRefundedOrderStatus(string $status, int $orderId, int $refundId): string {
		
			// Get order
			$order = wc_get_order($orderId);
			
			// Check if order exists and used this payment method
			if($order !== FALSE && $order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID) {
			
				// Return nothing so that status won't be updated
				return "";
			}
			
			// Otherwise
			else {
			
				// Return status
				return $status;
			}
		}
		
		// Prevent changing payment method
		public static function WC_Gateway_MWC_Pay_preventChangingPaymentMethod(array $paymentMethods): array {
		
			// Check if at the order pay page and an order ID exists
			$orderId = absint(get_query_var("order-pay"));
			if($orderId > 0) {
			
				// Check if getting order was successful
				$order = wc_get_order($orderId);
				if($order !== FALSE) {
				
					// Check if order didn't use this payment method
					if($order->get_payment_method() !== self::WC_Gateway_MWC_Pay_ID) {
					
						// Remove own payment method from payment methods
						unset($paymentMethods[self::WC_Gateway_MWC_Pay_ID]);
					}
					
					// Otherwise
					else {
					
						// Return only own payment method
						return array_filter($paymentMethods, function(string $paymentMethodId): bool {
			
							// Return if payment method is self
							return $paymentMethodId === self::WC_Gateway_MWC_Pay_ID;
							
						}, ARRAY_FILTER_USE_KEY);
					}
				}
			}
			
			// Return payment methods
			return $paymentMethods;
		}
		
		// Add currency
		public static function WC_Gateway_MWC_Pay_addCurrency(array $currencies): array {
		
			// Add currency to currencies
			$currencies[self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID] = esc_html__("MimbleWimble Coin", "mwc-pay-woocommerce-extension");
			
			// Return currencies
			return $currencies;
		}
		
		// Add currency symbol
		public static function WC_Gateway_MWC_Pay_addCurrencySymbol(array $symbols): array {
		
			// Add currency symbol to symbols
			$symbols[self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID] = self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_SYMBOL;
			
			// Return symbols
			return $symbols;
		}
		
		// Remove trailing zeros server side
		public static function WC_Gateway_MWC_Pay_removeTrailingZerosServerSide(string $formattedPrice, float $price, int $decimals, string $decimalSeparator, string $thousandSeparator, float | string $originalPrice): string {
		
			// Check if formatted price has decimals and currency is MimbleWimble Coin
			if($decimals > 0 && get_woocommerce_currency() === self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID) {
			
				// Return formatted price without trailing zeros and decimal separator
				return rtrim(rtrim($formattedPrice, "0"), $decimalSeparator);
			}
			
			// Otherwise
			else {
			
				// Return formatted price
				return $formattedPrice;
			}
		}
		
		// Remove trailing zeros client side
		public static function WC_Gateway_MWC_Pay_removeTrailingZerosClientSide(array $pluginData): void {
		
			// Check if currency is MimbleWimble Coin
			if(get_woocommerce_currency() === self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID) {
			
				// Add remove trailing zeros client side script
				wp_enqueue_script("MwcPayWooCommerceExtension_remove_trailing_zeros_client_side_script", plugins_url("../assets/js/remove_trailing_zeros_client_side.min.js", __FILE__), ["react"], $pluginData["Version"], TRUE);
			}
		}
		
		// Save order decimals in database
		public static function WC_Gateway_MWC_Pay_saveOrderDecimalsInDatabase(array $rows, mixed $order, string $context): array {
		
			// Check if order's currency is MimbleWimble Coin
			if($order->get_currency() === self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID) {
			
				// Add order row
				$rows[] = [
				
					// Table
					"table" => Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore::get_orders_table_name(),
					
					// Data
					"data" => [
					
						// ID
						"id" => $order->get_id(),
						
						// Tax amount
						"tax_amount" => sprintf("%." . self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS . "F", $order->get_cart_tax()),
						
						// Total amount
						"total_amount" => sprintf("%." . self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS . "F", $order->get_total())
					],
					
					// Format
					"format" => [
					
						// ID
						"id" => "%d",
						
						// Tax amount
						"tax_amount" => "%s",
						
						// Tax amount
						"total_amount" => "%s"
					]
				];
				
				// Add operational data row
				$rows[] = [
				
					// Table
					"table" => Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore::get_operational_data_table_name(),
					
					// Data
					"data" => [
					
						// Order ID
						"order_id" => $order->get_id(),
						
						// Shipping tax amount
						"shipping_tax_amount" => sprintf("%." . self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS . "F", $order->get_shipping_tax()),
						
						// Shipping total amount
						"shipping_total_amount" => sprintf("%." . self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS . "F", $order->get_shipping_total()),
						
						// Discount tax amount
						"discount_tax_amount" => sprintf("%." . self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS . "F", $order->get_discount_tax()),
						
						// Discount total amount
						"discount_total_amount" => sprintf("%." . self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS . "F", $order->get_discount_total())
					],
					
					// Format
					"format" => [
					
						// Order ID
						"order_id" => "%d",
						
						// Shipping tax amount
						"shipping_tax_amount" => "%s",
						
						// Shipping total amount
						"shipping_total_amount" => "%s",
						
						// Discount tax amount
						"discount_tax_amount" => "%s",
						
						// Discount total amount
						"discount_total_amount" => "%s"
					]
				];
			}
			
			// Return rows
			return $rows;
		}
		
		// Maybe increase stock on failed order
		public static function WC_Gateway_MWC_Pay_maybeIncreaseStockOnFailedOrder(int $orderId): void {
		
			// Get order
			$order = wc_get_order($orderId);
			
			// Check if order exists, used this payment method, and didn't fail for taking too long to achieve enough block confirmations
			if($order !== FALSE && $order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID && $order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_can_be_processed") === "") {
			
				// Maybe increase stock levels
				wc_maybe_increase_stock_levels($orderId);
			}
		}
		
		// Release stock on failed order
		public static function WC_Gateway_MWC_Pay_releaseStockOnFailedOrder(mixed $orderOrId): void {
		
			// Get order
			$order = wc_get_order($orderOrId);
			
			// Check if order exists, used this payment method, and didn't fail for taking too long to achieve enough block confirmations
			if($order !== FALSE && $order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID && $order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_can_be_processed") === "") {
			
				// Release stock for order
				wc_release_stock_for_order($orderOrId);
			}
		}
		
		// Prevent automatically cancelling unpaid orders
		public static function WC_Gateway_MWC_Pay_preventAutomaticallyCancellingUnpaidOrders(bool $cancel, mixed $order): bool {
		
			// Check if order is used payment method
			if($order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID) {
			
				// Return false
				return FALSE;
			}
			
			// Otherwise
			else {
			
				// Return cancel
				return $cancel;
			}
		}
		
		// Limit editing orders
		public static function WC_Gateway_MWC_Pay_limitEditingOrders(bool $edit, mixed $order): bool {
		
			// Check if order used this payment method
			if($order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID) {
			
				// Return false
				return FALSE;
			}
			
			// Otherwise
			else {
			
				// Return edit
				return $edit;
			}
		}
		
		// Show number of block confirmations in order details
		public static function WC_Gateway_MWC_Pay_showNumberOfBlockConfirmationsInOrderDetails(mixed $order): void {
		
			// Check if order used this payment method
			if($order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID) {
			
				// Check if order has a required number of block confirmations
				if($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations") !== "") {
			
					// Check if order is pending payment
					if($order->has_status("pending") === TRUE) {
					
						// Display number of block confirmations message
						echo "<p>" . sprintf(esc_html(_n('This order will start processing once its payment achieves %1$s block confirmation %2$s minute%3$s.', 'This order will start processing once its payment achieves %1$s block confirmations %2$s minutes%3$s.', (int)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations"), "mwc-pay-woocommerce-extension")), "<bdi><strong>" . esc_html(number_format((float)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations"), 0, wc_get_price_decimal_separator(), wc_get_price_thousand_separator())) . "</strong></bdi>", "<bdi>(<strong>≈&zwj;" . esc_html(number_format((float)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations"), 0, wc_get_price_decimal_separator(), wc_get_price_thousand_separator())) . "</strong></bdi><strong>", "</strong><bdi>)</bdi>") . "</p>";
					}
					
					// Otherwise check if order is on-hold and it has a current number of block confirmations
					else if($order->has_status("on-hold") === TRUE && $order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_current_number_of_block_confirmations") !== "") {
					
						// Get minutes remaining
						$minutesRemaining = max((int)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations") - (int)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_current_number_of_block_confirmations"), 0);
						
						// Display number of block confirmations message
						echo "<p>" . sprintf(esc_html(_n('This order will start processing once its payment achieves %1$s / %2$s block confirmations %3$s minute remaining%4$s.', 'This order will start processing once its payment achieves %1$s / %2$s block confirmations %3$s minutes remaining%4$s.', $minutesRemaining, "mwc-pay-woocommerce-extension")), "<bdi><strong>" . esc_html(number_format((float)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_current_number_of_block_confirmations"), 0, wc_get_price_decimal_separator(), wc_get_price_thousand_separator())) . "</strong></bdi>", "<bdi><strong>" . esc_html(number_format((float)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations"), 0, wc_get_price_decimal_separator(), wc_get_price_thousand_separator())) . "</strong></bdi>", "<bdi>(<strong>≈&zwj;" . esc_html(number_format((float)$minutesRemaining, 0, wc_get_price_decimal_separator(), wc_get_price_thousand_separator())) . "</strong></bdi><strong>", "</strong><bdi>)</bdi>") . "</p>";
					}
					
					// Otherwise check if order is processing or completed
					else if($order->has_status(["processing", "completed"]) === TRUE) {
					
						// Display number of block confirmations message
						echo "<p>" . sprintf(esc_html__('This order\'s payment has achieved %1$s / %1$s block confirmations.', "mwc-pay-woocommerce-extension"), "<bdi><strong>" . esc_html(number_format((float)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations"), 0, wc_get_price_decimal_separator(), wc_get_price_thousand_separator())) . "</strong></bdi>") . "</p>";
					}
				}
				
				// Check if order has a recipient payment proof address
				if($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_recipient_payment_proof_address") !== "") {
				
					// Display recipient payment proof address message
					echo "<p>" . sprintf(esc_html__("The recipient payment proof address for this order is %s.", "mwc-pay-woocommerce-extension"), "<bdi style=\"word-break: break-all;\"><strong>" . esc_html($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_recipient_payment_proof_address")) . "</strong></bdi>") . "</p>";
				}
			}
		}
		
		// Show price in order details
		public static function WC_Gateway_MWC_Pay_showPriceInOrderDetails(array $totalRows, mixed $order, string $taxDisplay): array {
		
			// Check if order used this payment method, it's currency isn't MimbleWimble Coin, and it has a price in MimbleWimble Coin
			if($order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID && $order->get_currency() !== self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID && $order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_price_in_mimblewimble_coin") !== "") {
			
				// Get amount of MimbleWimble Coin after refunds
				$amountOfMimbleWimbleCoinAfterRefunds = ($order->get_total() <= 0) ? "0" : rtrim(rtrim((string)Brick\Math\BigDecimal::of($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_price_in_mimblewimble_coin"))->minus(Brick\Math\BigDecimal::of($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_price_in_mimblewimble_coin"))->multipliedBy($order->get_total_refunded())->dividedBy($order->get_total(), self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS, Brick\Math\RoundingMode::DOWN)), "0"), ".");
				
				// Add amount to order's total
				$totalRows["order_total"]["value"] .= " <bdi>(" . self::WC_Gateway_MWC_Pay_localizeLargeNumber($amountOfMimbleWimbleCoinAfterRefunds) . ")</bdi>";
				
				// Go through all of the order's refunds
				$refunds = $order->get_refunds();
				foreach($refunds as $id => $refund) {
				
					// Check if at the most recent refund and the order is fully refunded
					if($id === array_key_first($refunds) && $order->has_status("refunded") === TRUE) {
					
						// Get amount of MimbleWimble Coin refunded
						$amountOfMimbleWimbleCoinRefunded = ($order->get_total() <= 0) ? "0" : rtrim(rtrim((string)Brick\Math\BigDecimal::of($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_price_in_mimblewimble_coin"))->minus(Brick\Math\BigDecimal::of($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_price_in_mimblewimble_coin"))->multipliedBy(Brick\Math\BigDecimal::of($order->get_total_refunded())->minus($refund->get_amount()))->dividedBy($order->get_total(), self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS, Brick\Math\RoundingMode::DOWN)), "0"), ".");
					}
					
					// Otherwise
					else {
					
						// Get amount of MimbleWimble Coin refunded
						$amountOfMimbleWimbleCoinRefunded = ($order->get_total() <= 0) ? "0" : rtrim(rtrim((string)Brick\Math\BigDecimal::of($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_price_in_mimblewimble_coin"))->multipliedBy($refund->get_amount())->dividedBy($order->get_total(), self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS, Brick\Math\RoundingMode::DOWN), "0"), ".");
					}
					
					// Add amount to order's refund
					$totalRows["refund_$id"]["value"] .= " <bdi>(–&zwj;" . self::WC_Gateway_MWC_Pay_localizeLargeNumber($amountOfMimbleWimbleCoinRefunded) . ")</bdi>";
				}
			}
			
			// Return total rows
			return $totalRows;
		}
		
		// Remove can be processed if processed
		public static function WC_Gateway_MWC_Pay_removeCanBeProcessedIfProcessed(int $orderId): void {
		
			// Get order
			$order = wc_get_order($orderId);
			
			// Check if order exists and used this payment method
			if($order !== FALSE && $order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID) {
			
				// Set that order can't be processed
				$order->delete_meta_data("_" . self::WC_Gateway_MWC_Pay_ID . "_can_be_processed");
				
				// Save order
				$order->save();
			}
		}
		
		// Show order info to admin
		public static function WC_Gateway_MWC_Pay_showOrderInfoToAdmin(mixed $order): void {
		
			// Check if order used this payment method
			if($order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID) {
			
				// Set info displayed to false
				$infoDisplayed = FALSE;
				
				// Check if order has a price in MimbleWimble Coin and order's currency isn't MimbleWimble Coin
				if($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_price_in_mimblewimble_coin") !== "" && $order->get_currency() !== self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID) {
				
					// Check if info hasn't been displayed
					if($infoDisplayed === FALSE) {
					
						// Set info displayed to true
						$infoDisplayed = TRUE;
						
						// Display start of info
						echo "<p class=\"form-field form-field-wide\">";
					}
					
					// Display order's total price in MimbleWimble Coin
					echo "<strong>" . esc_html__("Total price in MimbleWimble Coin:", "mwc-pay-woocommerce-extension") . "</strong> <bdi>" . self::WC_Gateway_MWC_Pay_localizeLargeNumber($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_price_in_mimblewimble_coin")) . "</bdi><br>";
					
					// Get amount of MimbleWimble Coin refunded
					$amountOfMimbleWimbleCoinRefunded = ($order->get_total() <= 0) ? "0" : rtrim(rtrim((string)Brick\Math\BigDecimal::of($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_price_in_mimblewimble_coin"))->multipliedBy($order->get_total_refunded())->dividedBy($order->get_total(), self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS, Brick\Math\RoundingMode::DOWN), "0"), ".");
					
					// Display order's total refunded in MimbleWimble Coin
					echo "<strong>" . esc_html__("Total refunded in MimbleWimble Coin:", "mwc-pay-woocommerce-extension") . "</strong> <bdi>" . self::WC_Gateway_MWC_Pay_localizeLargeNumber($amountOfMimbleWimbleCoinRefunded) . "</bdi><br>";
				}
				
				// Check if order has a required number of block confirmations
				if($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations") !== "") {
				
					// Check if info hasn't been displayed
					if($infoDisplayed === FALSE) {
					
						// Set info displayed to true
						$infoDisplayed = TRUE;
						
						// Display start of info
						echo "<p class=\"form-field form-field-wide\">";
					}
					
					// Display order's required number of block confirmations
					echo "<strong>" . esc_html__("Required number of block confirmations:", "mwc-pay-woocommerce-extension") . "</strong> <bdi>" . esc_html(number_format((float)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations"), 0, wc_get_price_decimal_separator(), wc_get_price_thousand_separator())) . "</bdi><br>";
				}
				
				// Check if order has a current number of block confirmations
				if($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_current_number_of_block_confirmations") !== "") {
				
					// Check if info hasn't been displayed
					if($infoDisplayed === FALSE) {
					
						// Set info displayed to true
						$infoDisplayed = TRUE;
						
						// Display start of info
						echo "<p class=\"form-field form-field-wide\">";
					}
					
					// Display order's current number of block confirmations
					echo "<strong>" . esc_html__("Current number of block confirmations:", "mwc-pay-woocommerce-extension") . "</strong> <bdi>" . esc_html(number_format((float)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_current_number_of_block_confirmations"), 0, wc_get_price_decimal_separator(), wc_get_price_thousand_separator())) . "</bdi><br>";
				}
				
				// Check if order has a payment ID
				if($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_payment_id") !== "") {
				
					// Check if info hasn't been displayed
					if($infoDisplayed === FALSE) {
					
						// Set info displayed to true
						$infoDisplayed = TRUE;
						
						// Display start of info
						echo "<p class=\"form-field form-field-wide\">";
					}
					
					// Display order's payment ID
					echo "<strong>" . esc_html__("MWC Pay payment ID:", "mwc-pay-woocommerce-extension") . "</strong> <bdi style=\"word-break: break-all;\">" . esc_html($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_payment_id")) . "</bdi><br>";
				}
				
				// Check if order has a kernel commitment
				if($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_kernel_commitment") !== "") {
				
					// Check if info hasn't been displayed
					if($infoDisplayed === FALSE) {
					
						// Set info displayed to true
						$infoDisplayed = TRUE;
						
						// Display start of info
						echo "<p class=\"form-field form-field-wide\">";
					}
					
					// Display order's kernel commitment
					echo "<strong>" . esc_html__("Kernel excess:", "mwc-pay-woocommerce-extension") . "</strong> <bdi style=\"word-break: break-all;\"><a href=\"" . esc_url(self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_BLOCK_EXPLORER_URL . $order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_kernel_commitment")) . "\" aria-label=\"" . esc_attr__("View order's payment in a block explorer", "mwc-pay-woocommerce-extension") . "\" target=\"_blank\" rel=\"nofollow noopener noreferrer\">" . esc_html($order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_kernel_commitment")) . "</a></bdi><br>";
				}
				
				// Check if info was displayed
				if($infoDisplayed === TRUE) {
				
					// Display end of info
					echo "</p>";
				}
			}
		}
		
		// Show number of block confirmations in order emails
		public static function WC_Gateway_MWC_Pay_showNumberOfBlockConfirmationsInOrderEmails(mixed $order, bool $sentToAdmin, bool $plainText, mixed $email): void {
		
			// Check if order used this payment method and order has a required number of block confirmations
			if($order->get_payment_method() === self::WC_Gateway_MWC_Pay_ID && $order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations") !== "") {
			
				// Check if order is on-hold
				if($order->has_status("on-hold") === TRUE) {
			
					// Check if not plain text
					if($plainText === FALSE) {
					
						// Display start of message
						echo "<p>";
					}
					
					// Display message
					echo sprintf(esc_html(_n('This order will start processing once its payment achieves %1$s block confirmation %2$s minute remaining%3$s.', 'This order will start processing once its payment achieves %1$s block confirmations %2$s minutes remaining%3$s.', (int)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations"), "mwc-pay-woocommerce-extension")), (($plainText === FALSE) ? "<bdi>" : "") . esc_html(number_format((float)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations"), 0, wc_get_price_decimal_separator(), wc_get_price_thousand_separator())) . (($plainText === FALSE) ? "</bdi>" : ""), (($plainText === FALSE) ? "<bdi>(≈&zwj;" : "(≈‍") . esc_html(number_format((float)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations"), 0, wc_get_price_decimal_separator(), wc_get_price_thousand_separator())) . (($plainText === FALSE) ? "</bdi>" : ""), (($plainText === FALSE) ? "<bdi>)</bdi>" : ")"));
					
					// Check if not plain text
					if($plainText === FALSE) {
					
						// Display end of message
						echo "</p>";
					}
					
					// Otherwise
					else {
					
						// Display end of line
						echo "\n\n";
					}
				}
				
				// Otherwise check if order is processing or completed
				else if($order->has_status(["processing", "completed"]) === TRUE) {
				
					// Check if not plain text
					if($plainText === FALSE) {
					
						// Display start of message
						echo "<p>";
					}
					
					// Display message
					echo sprintf(esc_html(_n("This order's payment has achieved %s block confirmation.", "This order's payment has achieved %s block confirmations.", (int)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations"), "mwc-pay-woocommerce-extension")), (($plainText === FALSE) ? "<bdi>" : "") . esc_html(number_format((float)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations"), 0, wc_get_price_decimal_separator(), wc_get_price_thousand_separator())) . (($plainText === FALSE) ? "</bdi>" : ""));
					
					// Check if not plain text
					if($plainText === FALSE) {
					
						// Display end of message
						echo "</p>";
					}
					
					// Otherwise
					else {
					
						// Display end of line
						echo "\n\n";
					}
				}
				
				// Otherwise check if email is sent to admin and the order failed for taking too long to achieve enough block confirmations
				else if($sentToAdmin === TRUE && $order->has_status("failed") === TRUE && $order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_can_be_processed") !== "") {
				
					// Check if not plain text
					if($plainText === FALSE) {
					
						// Display start of message
						echo "<p>";
					}
					
					// Display message
					echo sprintf(esc_html(_n("This order's payment has achieved %s block confirmation but not within the allowed amount of time. The customer either nefariously delayed broadcasting the order's payment or the MimbleWimble Coin network is congested. You can choose to either refund or process this order.", "This order's payment has achieved %s block confirmations but not within the allowed amount of time. The customer either nefariously delayed broadcasting the order's payment or the MimbleWimble Coin network is congested. You can choose to either refund or process this order.", (int)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations"), "mwc-pay-woocommerce-extension")), (($plainText === FALSE) ? "<bdi>" : "") . esc_html(number_format((float)$order->get_meta("_" . self::WC_Gateway_MWC_Pay_ID . "_required_number_of_block_confirmations"), 0, wc_get_price_decimal_separator(), wc_get_price_thousand_separator())) . (($plainText === FALSE) ? "</bdi>" : ""));
					
					// Check if not plain text
					if($plainText === FALSE) {
					
						// Display end of message
						echo "</p>";
					}
					
					// Otherwise
					else {
					
						// Display end of line
						echo "\n\n";
					}
				}
			}
		}
		
		// Is connection secure
		private static function WC_Gateway_MWC_Pay_isConnectionSecure(): bool {
		
			// Check if site is an Onion Service
			if(preg_match('/\.onion$/ui', get_site_url()) === 1) {
			
				// Return true
				return TRUE;
			}
			
			// Otherwise check if site is behind a proxy or load balancer and client connected to it securely
			else if(isset($_SERVER) === TRUE && array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER) === TRUE && $_SERVER["HTTP_X_FORWARDED_PROTO"] === "https") {
			
				// Return true
				return TRUE;
			}
			
			// Otherwise
			else {
			
				// Return if connection is secure
				return is_ssl() === TRUE;
			}
		}
		
		// Localize large number
		private static function WC_Gateway_MWC_Pay_localizeLargeNumber(string $largeNumber, bool $copyable = FALSE): string {
		
			// Get large number's components
			$components = preg_split('/\./u', $largeNumber, 2);
			
			// Localize large number
			$localizedLargeNumber = number_format((float)$components[0], 0, wc_get_price_decimal_separator(), wc_get_price_thousand_separator()) . ((count($components) === 1) ? "" : rtrim(trim(number_format((float)".{$components[1]}", self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS, wc_get_price_decimal_separator(), wc_get_price_thousand_separator()), "0"), wc_get_price_decimal_separator()));
			
			// Check if copyable
			if($copyable === TRUE) {
			
				// Return copyable localized large number with currency symbol
				return "<bdi>" . sprintf((get_woocommerce_currency() === self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID) ? preg_replace('/%2\$s/u', '<span title="%3$s">%2$s</span>', get_woocommerce_price_format()) : '<span title="%3$s">%2$s</span>&nbsp;%1$s', esc_html(get_woocommerce_currency_symbol(self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID)), esc_html($localizedLargeNumber), esc_attr__("Copy amount", "mwc-pay-woocommerce-extension")) . "</bdi>";
			}
			
			// Otherwise
			else {
			
				// Return localized large number with currency symbol
				return sprintf((get_woocommerce_currency() === self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID) ? get_woocommerce_price_format() : '%2$s&nbsp;%1$s', esc_html(get_woocommerce_currency_symbol(self::WC_Gateway_MWC_Pay_MIMBLEWIMBLE_COIN_CURRENCY_ID)), esc_html($localizedLargeNumber));
			}
		}
	}
	
	// Add translations and blocks
	add_action("init", "MwcPayWooCommerceExtension::addTranslationsAndBlocks");
	
	// Add base script
	add_action("init", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_addBaseScript");
	
	// Add settings link
	add_filter("plugin_action_links_$pluginBasename", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_addSettingsLink");
	
	// Add checkout scripts and styles
	add_action("wp_enqueue_scripts", fn(): mixed => WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_addCheckoutScriptsAndStyles($pluginData));
	
	// Add checkout content
	add_action("wp_footer", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_addCheckoutContent");
	
	// Add payment gateway
	add_filter("woocommerce_payment_gateways", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_addPaymentGateway");
	
	// Add payment blocks
	add_action("woocommerce_blocks_loaded", fn(): mixed => WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_addPaymentBlocks($pluginData));
	
	// Remove pay and cancel order buttons
	add_filter("woocommerce_my_account_my_orders_actions", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_removePayAndCancelOrderButtons", 999, 2);
	
	// Limit cancelling orders
	add_filter("woocommerce_valid_order_statuses_for_cancel", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_limitCancellingOrders", 999, 2);
	
	// Limit changing individual order status
	add_filter("wc_order_statuses", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_limitChangingIndividualOrderStatus", 999);
	
	// Limit changing bulk order status
	add_filter("bulk_actions-woocommerce_page_wc-orders", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_limitChangingBulkOrderStatus", 999);
	add_filter("bulk_actions-edit-shop_order", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_limitChangingBulkOrderStatus", 999);
	
	// Limit refunding orders
	add_filter("woocommerce_admin_order_should_render_refunds", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_limitRefundingOrders", 999, 3);
	
	// Add partially refunded order note
	add_action("woocommerce_order_partially_refunded", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_addPartiallyRefundedOrderNote", 0, 2);
	
	// Add fully refunded order note
	add_action("woocommerce_order_fully_refunded", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_addFullyRefundedOrderNote", 0, 2);
	
	// Prevent automatically setting fully refunded order status
	add_filter("woocommerce_order_fully_refunded_status", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_preventAutomaticallySettingFullyRefundedOrderStatus", 999, 3);
	
	// Prevent changing payment method
	add_filter("woocommerce_available_payment_gateways", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_preventChangingPaymentMethod", 999);
	
	// Add currency
	add_filter("woocommerce_currencies", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_addCurrency");
	
	// Add currency symbol
	add_filter("woocommerce_currency_symbols", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_addCurrencySymbol");
	
	// Remove trailing zeros server side
	add_filter("formatted_woocommerce_price", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_removeTrailingZerosServerSide", 999, 6);
	
	// Remove trailing zeros client side
	add_filter("init", fn(): mixed => WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_removeTrailingZerosClientSide($pluginData));
	
	// Save order decimals in database
	add_filter("woocommerce_orders_table_datastore_extra_db_rows_for_order", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_saveOrderDecimalsInDatabase", 999, 3);
	
	// Maybe increase stock on failed order
	add_action("woocommerce_order_status_failed", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_maybeIncreaseStockOnFailedOrder");
	
	// Release stock on failed order
	add_action("woocommerce_order_status_failed", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_releaseStockOnFailedOrder", 11);
	
	// Prevent automatically cancelling unpaid orders
	add_filter("woocommerce_cancel_unpaid_order", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_preventAutomaticallyCancellingUnpaidOrders", 999, 2);
	
	// Limit editing orders
	add_filter("wc_order_is_editable", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_limitEditingOrders", 999, 2);
	
	// Show number of block confirmations in order details
	add_action("woocommerce_order_details_before_order_table", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_showNumberOfBlockConfirmationsInOrderDetails");
	
	// Show price in order details
	add_filter("woocommerce_get_order_item_totals", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_showPriceInOrderDetails", 999, 3);
	
	// Remove can be processed if processed
	add_action("woocommerce_order_status_failed_to_processing", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_removeCanBeProcessedIfProcessed");
	add_action("woocommerce_order_status_failed_to_completed", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_removeCanBeProcessedIfProcessed");
	add_action("woocommerce_order_status_failed_to_refunded", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_removeCanBeProcessedIfProcessed");
	
	// Show order info to admin
	add_action("woocommerce_admin_order_data_after_order_details", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_showOrderInfoToAdmin");
	
	// Show number of block confirmations in order emails
	add_action("woocommerce_email_order_details", "WC_Gateway_MWC_Pay::WC_Gateway_MWC_Pay_showNumberOfBlockConfirmationsInOrderEmails", 0, 4);
}


?>
