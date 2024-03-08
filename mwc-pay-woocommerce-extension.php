<?php
/**
 * Plugin Name: MWC Pay WooCommerce Extension
 * Plugin URI: https://github.com/NicolasFlamel1/MWC-Pay-WooCommerce-Extension
 * Description: MWC Pay extension for WooCommerce that allows WordPress sites to accept MimbleWimble Coin payments.
 * Version: 0.1.5
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * WC requires at least: 8.6
 * Author: Nicolas Flamel
 * License: MIT
 * License URI: https://github.com/NicolasFlamel1/MWC-Pay-WooCommerce-Extension/blob/master/LICENSE
 * Text Domain: mwc-pay-woocommerce-extension
 * Domain Path: /languages
*/


// Enforce strict types
declare(strict_types=1);


// Check if file is accessed directly
if(defined("ABSPATH") === FALSE) {

	// Exit
	exit;
}

// Check if MWC Pay WooCommerce extension class doesn't exist
if(class_exists("MwcPayWooCommerceExtension") === FALSE) {

	// MWC Pay WooCommerce extension class
	final class MwcPayWooCommerceExtension {
	
		// MimbleWimble Coin number of decimal digits
		public const MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS = 9;
		
		// WooCommerce plugin file
		private const WOOCOMMERCE_PLUGIN_FILE = "woocommerce/woocommerce.php";
		
		// Blocks category ID
		private const BLOCKS_CATEGORY_ID = "mwc-pay-woocommerce-extension";
		
		// Currency exchange rates table name
		private const CURRENCY_EXCHANGE_RATES_TABLE_NAME = "mwc_pay_woocommerce_extension_currency_exchange_rates";
		
		// Update currency exchange rates action name
		private const UPDATE_CURRENCY_EXCHANGE_RATES_ACTION_NAME = "mwc_pay_woocommerce_extension_update_currency_exchange_rates";
		
		// Exchange rate API URL (Exchange rates provided by Exchange Rate API - https://www.exchangerate-api.com)
		private const EXCHANGE_RATE_API_URL = "https://open.er-api.com/v6/latest/USD";
		
		// Columns to increase decimals
		private array $columnsToIncreaseDecimals;
		
		// Constructor
		public function __construct() {
		
			// Include dependencies
			require_once ABSPATH . "wp-admin/includes/plugin.php";
			
			// Create currency exchange rates table in database
			register_activation_hook(__FILE__, "MwcPayWooCommerceExtension::createCurrencyExchangeRatesTableInDatabase");
			
			// Remove currency exchange rates table in database
			register_uninstall_hook(__FILE__, "MwcPayWooCommerceExtension::removeCurrencyExchangeRatesTableInDatabase");
			
			// Update currency exchange rates
			add_action(self::UPDATE_CURRENCY_EXCHANGE_RATES_ACTION_NAME, "MwcPayWooCommerceExtension::updateCurrencyExchangeRates");
			
			// Schedule updating currency exchange rates
			register_activation_hook(__FILE__, "MwcPayWooCommerceExtension::scheduleUpdatingCurrencyExchangeRates");
			
			// Unschedule updating currency exchange rates
			register_deactivation_hook(__FILE__, "MwcPayWooCommerceExtension::unscheduleUpdatingCurrencyExchangeRates");
						
			// Check if WooCommerce plugin isn't installed and activated
			if(is_plugin_active(self::WOOCOMMERCE_PLUGIN_FILE) === FALSE) {

				// Add translations and blocks
				add_action("init", "MwcPayWooCommerceExtension::addTranslationsAndBlocks");
				
				// Warn about WooCommerce plugin requirement
				add_action("admin_notices", "MwcPayWooCommerceExtension::warnAboutWooCommercePluginRequirement");
			}
			
			// Otherwise
			else {
			
				// Increase order decimals storage in existing database
				register_activation_hook(__FILE__, [$this, "increaseOrderDecimalsStorageInExistingDatabase"]);
				
				// Increase order decimals storage in database
				add_action("plugins_loaded", [$this, "increaseOrderDecimalsStorageInDatabase"], 0);
				
				// Initialize payment gateway
				add_action("plugins_loaded", "MwcPayWooCommerceExtension::initializePaymentGateway");
			}
		}
		
		// Increase order decimals storage in existing database
		public function increaseOrderDecimalsStorageInExistingDatabase(): void {
		
			// Set columns to increase decimals
			$this->columnsToIncreaseDecimals = self::getColumnsToIncreaseDecimals();
			
			// Use WordPress database
			global $wpdb;
			
			// Check if WordPress database doesn't exist
			if(isset($wpdb) === FALSE) {
			
				// Throw error
				throw new Exception("Altering WooCommerce database failed");
			}
			
			// Go through all tables with columns to increase decimals
			foreach($this->columnsToIncreaseDecimals as $table => $columns) {
			
				// Go through all columns to increase
				foreach($columns as $column) {
				
					// Check if getting if table exists failed
					$tableExists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s;", $wpdb->dbname, $table));
					if($tableExists === NULL) {
					
						// Throw error
						throw new Exception("Altering WooCommerce database failed");
					}
					
					// Check if table exists
					if((int)$tableExists === 1) {
					
						// Check if getting column info failed
						$columnInfo = $wpdb->get_row($wpdb->prepare("SELECT NUMERIC_PRECISION, NUMERIC_SCALE FROM information_schema.columns WHERE table_schema = %s AND table_name = %s AND column_name = %s;", $wpdb->dbname, $table, $column));
						if($columnInfo === NULL) {
						
							// Throw error
							throw new Exception("Altering WooCommerce database failed");
						}
						
						// Check if column's numeric scale is too low
						if((int)$columnInfo->NUMERIC_SCALE < self::MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS) {
						
							// Check if increasing column's numeric scale failed
							if($wpdb->query("ALTER TABLE $table MODIFY COLUMN $column DECIMAL(" . ((int)$columnInfo->NUMERIC_PRECISION + self::MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS - (int)$columnInfo->NUMERIC_SCALE) . "," . self::MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS . ") NULL;") === FALSE) {
							
								// Throw error
								throw new Exception("Altering WooCommerce database failed");
							}
						}
					}
				}
			}
		}
		
		// Increase order decimal storage in database
		public function increaseOrderDecimalsStorageInDatabase(): void {
		
			// Set columns to increase decimals
			$this->columnsToIncreaseDecimals = self::getColumnsToIncreaseDecimals();
			
			// Increase order decimals storage in new database
			add_filter("dbdelta_create_queries", [$this, "increaseOrderDecimalsStorageInNewDatabase"], 999);
		}
		
		// Increase order decimals storage in new databases
		public function increaseOrderDecimalsStorageInNewDatabase(array $createQueries): array {
		
			// Go through all create queries
			foreach($createQueries as $name => &$query) {
			
				// Check if creating table that needs decimals increased
				if(array_key_exists($name, $this->columnsToIncreaseDecimals) === TRUE) {
				
					// Go through all columns to increase decimals
					foreach($this->columnsToIncreaseDecimals[$name] as $column) {
					
						// Create search
						$search = '/(?<=^\t' . preg_quote($column, "/") . ' DECIMAL\()(\d+),(\d+)(?=\) NULL,$)/uim';
						
						// Check if query doesn't contain the column
						if(preg_match($search, $query) !== 1) {
						
							// Throw error
							throw new Exception("Altering WooCommerce database failed");
						}
						
						// Check if increasing decimals in column failed
						$query = preg_replace_callback($search, function(array $matches): string {
						
							// Check if column's numeric scale is too low
							if((int)$matches[2] < self::MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS) {
							
								// Return changed column
								return ((int)$matches[1] + self::MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS - (int)$matches[2]) . "," . self::MIMBLEWIMBLE_COIN_NUMBER_OF_DECIMAL_DIGITS;
							}
							
							// Otherwise
							else {
							
								// Return unchanged column
								return $matches[0];
							}
							
						}, $query, 1);
						
						if($query === NULL) {
						
							// Throw error
							throw new Exception("Altering WooCommerce database failed");
						}
					}
				}
			}
			
			// Return create queries
			return $createQueries;
		}
		
		// Create currency exchange rates table in database
		public static function createCurrencyExchangeRatesTableInDatabase(): void {
		
			// Include dependencies
			require_once ABSPATH . "wp-admin/includes/upgrade.php";
			
			// Use WordPress database
			global $wpdb;
			
			// Check if WordPress database doesn't exist
			if(isset($wpdb) === FALSE) {
			
				// Throw error
				throw new Exception("Creating currency exchange rates table in database failed");
			}
			
			// Create currency exchange rates table
			dbDelta("CREATE TABLE {$wpdb->prefix}" . self::CURRENCY_EXCHANGE_RATES_TABLE_NAME . " (
				currency VARCHAR(30) NOT NULL,
				exchange_rate TINYTEXT NOT NULL,
				PRIMARY KEY (currency)
			) " . (($wpdb->has_cap("collation") === TRUE) ? $wpdb->get_charset_collate() : "") . ";");
			
			// Check if currency exchange rates table doesn't exist
			$tableExists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s;", $wpdb->dbname, $wpdb->prefix . self::CURRENCY_EXCHANGE_RATES_TABLE_NAME));
			if($tableExists === NULL || (int)$tableExists !== 1) {
			
				// Throw error
				throw new Exception("Creating currency exchange rates table in database failed");
			}
		}
		
		// Remove currency exchange rates table in database
		public static function removeCurrencyExchangeRatesTableInDatabase(): void {
		
			// Use WordPress database
			global $wpdb;
			
			// Check if WordPress database exists
			if(isset($wpdb) === TRUE) {
			
				// Remove currency exchange rates table
				$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}" . self::CURRENCY_EXCHANGE_RATES_TABLE_NAME . ";");
			}
		}
		
		// Schedule updating currency exchange rates
		public static function scheduleUpdatingCurrencyExchangeRates(): void {
		
			// Check if updating currency exchange rates isn't scheduled
			if(wp_next_scheduled(self::UPDATE_CURRENCY_EXCHANGE_RATES_ACTION_NAME) === FALSE) {
			
				// Schedule updating currency exchange rates
				wp_schedule_event(time(), "daily", self::UPDATE_CURRENCY_EXCHANGE_RATES_ACTION_NAME);
			}
		}
		
		// Unschedule updating currency exchange rates
		public static function unscheduleUpdatingCurrencyExchangeRates(): void {
		
			// Check if updating currency exchange rates is scheduled
			$timestamp = wp_next_scheduled(self::UPDATE_CURRENCY_EXCHANGE_RATES_ACTION_NAME);
			if($timestamp !== FALSE) {
			
				// Unschedule updating currency exchange rates
				wp_unschedule_event($timestamp, self::UPDATE_CURRENCY_EXCHANGE_RATES_ACTION_NAME);
			}
		}
		
		// Update currency exchange rates
		public static function updateCurrencyExchangeRates(): void {
		
			// Use WordPress database
			global $wpdb;
			
			// Check if WordPress database exists
			if(isset($wpdb) === TRUE) {
			
				// Send get exchange rates request
				$response = @file_get_contents(self::EXCHANGE_RATE_API_URL);
				
				// Check if performing request was successful
				if($response !== FALSE) {
				
					// Get response's data with numbers as strings
					$data = preg_replace('/(?<=:)[+-]?(?:0|[1-9]\d*)(?:\.\d+)?(?:e[+-]?\d+)?(?=[,}])/ui', "\"\\0\"", $response);
					
					// Try
					try {
					
						// Get exchange rates from data
						$exchangeRates = json_decode($data, TRUE, 3, JSON_THROW_ON_ERROR);
					}
					
					// Catch errors
					catch(Throwable $error) {
					
						// Return
						return;
					}
					
					// Check if exchange rates are valid
					if(array_key_exists("result", $exchangeRates) === TRUE && $exchangeRates["result"] === "success" && array_key_exists("base_code", $exchangeRates) === TRUE && $exchangeRates["base_code"] === "USD" && array_key_exists("rates", $exchangeRates) === TRUE && is_array($exchangeRates["rates"]) === TRUE) {
					
						// Set first valid exchange rate to true
						$firstValidExchangeRate = TRUE;
						
						// Create query
						$query = "REPLACE INTO {$wpdb->prefix}" . self::CURRENCY_EXCHANGE_RATES_TABLE_NAME . " VALUES ";
						
						// Go through all exchange rates
						foreach($exchangeRates["rates"] as $currency => $exchangeRate) {
						
							// Check if exchange rate is valid
							if(is_string($currency) === TRUE && $currency !== "" && is_string($exchangeRate) === TRUE && preg_match('/^[+-]?(?:0|[1-9]\d*)(?:\.\d+)?(?:e[+-]?\d+)?$/ui', $exchangeRate) === 1) {
							
								// Check if first valid exchange rate
								if($firstValidExchangeRate === TRUE) {
								
									// Set first valid exchange rate to false
									$firstValidExchangeRate = FALSE;
								}
								
								// Otherwise
								else {
								
									// Add separator to query
									$query .= ", ";
								}
								
								// Add currency and exchange rate to query
								$query .= "('" . esc_sql($currency) . "', '" . esc_sql($exchangeRate) . "')";
							}
						}
						
						// Check if a valid exchange rate exists
						if($firstValidExchangeRate === FALSE) {
						
							// Perform query on database
							$wpdb->query("$query;");
						}
					}
				}
			}
		}
		
		// Get currency exchange rate
		public static function getCurrencyExchangeRate(string $currency): ?string {
		
			// Use WordPress database
			global $wpdb;
			
			// Check if WordPress database doesn't exist
			if(isset($wpdb) === FALSE) {
			
				// Return null
				return NULL;
			}
			
			// Otherwise
			else {
			
				// Return exchange rate for currency from the database
				return $wpdb->get_var($wpdb->prepare("SELECT exchange_rate FROM {$wpdb->prefix}" . self::CURRENCY_EXCHANGE_RATES_TABLE_NAME . " WHERE currency = %s;", $currency));
			}
		}
		
		// Add translations and blocks
		public static function addTranslationsAndBlocks(): void {
		
			// Get plugin data
			$pluginData = get_plugin_data(__FILE__);
			
			// Add translations
			load_plugin_textdomain($pluginData["TextDomain"], FALSE, dirname(__FILE__) . $pluginData["DomainPath"]);
			
			// Add blocks script
			wp_enqueue_script("MwcPayWooCommerceExtension_blocks_script", plugins_url("assets/js/blocks.min.js", __FILE__), ["wp-blocks"], $pluginData["Version"], TRUE);
			
			// Load translations for blocks scripts
			wp_set_script_translations("MwcPayWooCommerceExtension_blocks_script", $pluginData["TextDomain"], dirname(__FILE__) . $pluginData["DomainPath"]);
			
			// Pass parameters to blocks script
			wp_add_inline_script("MwcPayWooCommerceExtension_blocks_script", "const MwcPayWooCommerceExtension_blocks_script_parameters = " . json_encode([
			
				// Category ID
				"category_id" => self::BLOCKS_CATEGORY_ID,
				
				// Category title
				"category_title" => __("MWC Pay WooCommerce Extension", "mwc-pay-woocommerce-extension"),
				
				// Blocks path
				"blocks_path" => plugins_url("src/blocks/", __FILE__)
				
			]), "before");
			
			// Add blocks
			register_block_type(plugin_dir_path(__FILE__) . "src/blocks/mimblewimble_coin_accepted_here_badge");
		}
		
		// Warn about WooCommerce plugin requirement
		public static function warnAboutWooCommercePluginRequirement(): void {
		
			// Check if WooCommerce plugin is installed
			if(array_key_exists(self::WOOCOMMERCE_PLUGIN_FILE, get_plugins()) === TRUE) {
			
				// Display warning
				echo "<div class=\"notice notice-warning is-dismissible\"><p>" . sprintf(esc_html__('MWC Pay WooCommerce Extension won\'t work unless you activate the WooCommerce plugin. Please activate the WooCommerce plugin from the %1$splugins page%2$s to resolve this issue.', "mwc-pay-woocommerce-extension"), "<a href=\"" . esc_url(admin_url("plugins.php")) . "\" aria-label=\"" . esc_attr__("Go to plugins page", "mwc-pay-woocommerce-extension") . "\">", "</a>") . "</p></div>";
			}
			
			// Otherwise
			else {
		
				// Display warning
				echo "<div class=\"notice notice-warning is-dismissible\"><p>" . sprintf(esc_html__('MWC Pay WooCommerce Extension won\'t work unless you install and activate the WooCommerce plugin. Please install and activate the WooCommerce plugin from the %1$sadd plugins page%2$s to resolve this issue.', "mwc-pay-woocommerce-extension"), "<a href=\"" . esc_url(admin_url("plugin-install.php?s=WooCommerce&tab=search")) . "\" aria-label=\"" . esc_attr__("Go to add plugins page", "mwc-pay-woocommerce-extension") . "\">", "</a>") . "</p></div>";
			}
		}
		
		// Initialize payment gateway
		public static function initializePaymentGateway(): void {
		
			// Get plugin basename
			$pluginBasename = plugin_basename(__FILE__);
			
			// Get plugin data
			$pluginData = get_plugin_data(__FILE__);
			
			// Include MWC Pay gateway
			require_once plugin_dir_path(__FILE__) . "includes/mwc_pay_gateway.php";
		}
		
		// Get columns to increase decimals
		private static function getColumnsToIncreaseDecimals(): array {
		
			// Return columns to increase decimals
			return [
			
				// Orders table
				Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore::get_orders_table_name() => [
				
					// Tax amount
					"tax_amount",
					
					// Total amount
					"total_amount"
				],
				
				// Operational data table
				Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore::get_operational_data_table_name() => [
				
					// Shipping tax amount
					"shipping_tax_amount",
					
					// Shipping total amount
					"shipping_total_amount",
					
					// Discount tax amount
					"discount_tax_amount",
					
					// Discount total amount
					"discount_total_amount"
				]
			];
		}
	}
	
	// Create MWC Pay WooCommerce extension
	$mwcPayWooCommerceExtension = new MwcPayWooCommerceExtension();
}


?>
