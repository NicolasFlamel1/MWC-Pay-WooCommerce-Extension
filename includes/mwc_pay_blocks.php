<?php


// Enforce strict types
declare(strict_types=1);


// Check if file is accessed directly
if(defined("ABSPATH") === FALSE) {

	// Exit
	exit;
}

// Check if plugin data and gateway ID exist and MWC Pay gateway blocks classes don't exist but its base class does
if(isset($pluginData) === TRUE && isset($gatewayId) === TRUE && class_exists("Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType") === TRUE && class_exists("WC_Gateway_MWC_Pay_Blocks") === FALSE) {

	// MWC Pay gateway blocks class
	final class WC_Gateway_MWC_Pay_Blocks extends Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
	
		// Plugin data
		private array $WC_Gateway_MWC_Pay_Blocks_pluginData;
		
		// Constructor
		public function __construct(array $pluginData, string $gatewayId) {
			
			// Define values
			$this->name = $gatewayId;
			
			// Set plugin data
			$this->WC_Gateway_MWC_Pay_Blocks_pluginData = $pluginData;
		}
		
		// Initialize
		public function initialize(): void {
		
			// Do nothing
		}
		
		// Is active
		public function is_active(): bool {
		
			// Get gateway
			$gateway = WC()->payment_gateways->payment_gateways()[$this->name];
			
			// Return if gateway is available
			return $gateway->is_available();
		}
		
		// Get supported features
		public function get_supported_features(): array {
		
			// Get gateway
			$gateway = WC()->payment_gateways->payment_gateways()[$this->name];
			
			// Return what gateway supports
			return $gateway->supports;
		}
		
		// Get payment method script handles
		public function get_payment_method_script_handles(): array {
		
			// Add scripts
			wp_register_script("MwcPayWooCommerceExtension_payment_blocks_script", plugins_url("../assets/js/payment_blocks.min.js", __FILE__), ["wp-element", "wc-blocks-registry", "wc-settings", "MwcPayWooCommerceExtension_base_script"], $this->WC_Gateway_MWC_Pay_Blocks_pluginData["Version"], TRUE);
			
			// Return payment method script handles
			return ["MwcPayWooCommerceExtension_payment_blocks_script"];
		}
		
		// Get payment method data
		public function get_payment_method_data(): array {
		
			// Get gateway
			$gateway = WC()->payment_gateways->payment_gateways()[$this->name];
			
			// Return payment method data
			return [
				
				// Gateway title
				"gateway_title" => $gateway->title,
				
				// Gateway description
				"gateway_description" => $gateway->description,
				
				// Gateway icon
				"gateway_icon" => $gateway->icon,
				
				// Gateway supports
				"gateway_supports" => $gateway->supports,
				
				// Gateway display format
				"gateway_display_format" => $gateway->WC_Gateway_MWC_Pay_displayFormat
			];
		}
	}
	
	// Register payment blocks
	add_action("woocommerce_blocks_payment_method_type_registration", fn(mixed $paymentMethodRegistry): bool => $paymentMethodRegistry->register(new WC_Gateway_MWC_Pay_Blocks($pluginData, $gatewayId)));
}


?>
