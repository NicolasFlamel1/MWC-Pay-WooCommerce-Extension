// Main function
(() => {

	// Use strict
	"use strict";
	
	// Check if payment method is enabled
	if(MwcPayWooCommerceExtension_checkout_script_parameters.enabled === true) {
		
		// Checkout container class
		let paymentMethodChangeEventMonitored = false;
		class CheckoutContainer extends React.Component {
		
			// Render
			render() {
			
				// Return container
				return React.createElement("div", {}, this.props.children);
			}
			
			// Component did mount
			componentDidMount() {
			
				// Check if payment method change event isn't being monitored
				if(paymentMethodChangeEventMonitored === false) {
				
					// Set payment method change event monitored to true
					paymentMethodChangeEventMonitored = true;
					
					// jQuery ready
					jQuery(async ($) => {
					
						// Payment this method change event
						let usingThisPaymentMethod = $("#radio-control-wc-payment-method-options-mwc_pay").is(":checked") === true;
						$(document).on("change", "#payment-method input[name=\"radio-control-wc-payment-method-options\"]", async () => {
						
							// Get new using this payment method
							const newUsingPaymentMethod = $("#radio-control-wc-payment-method-options-mwc_pay").is(":checked") === true;
							
							// Check if using this payment method changed
							if(newUsingPaymentMethod !== usingThisPaymentMethod) {
							
								// Update using this payment method
								usingThisPaymentMethod = newUsingPaymentMethod;
								
								// Check if using this payment method
								if(usingThisPaymentMethod === true) {
								
									// Show checkout total
									$("span#MwcPayWooCommerceExtension_checkout_total").removeClass("MwcPayWooCommerceExtension_checkout_hide");
								}
								
								// Otherwise
								else {
								
									// Hide checkout total
									$("span#MwcPayWooCommerceExtension_checkout_total").addClass("MwcPayWooCommerceExtension_checkout_hide");
								}
								
								// Check if payment method has a discount or surcharge
								if(MwcPayWooCommerceExtension_checkout_script_parameters.has_discount_or_surcharge === true) {
								
									// Try
									try {
									
										// Refresh cart
										await wp.data.dispatch(wc.wcBlocksData.CART_STORE_KEY).receiveCartContents(await wp.apiFetch({
										
											// Path
											path: "/wc/store/v1/cart",
											
											// Method
											method: "GET",
											
											// Cache
											cache: "no-store"
										}));
									}
									
									// Catch errors
									catch(error) {
									
									}
								}
							}
						});
						
						// Use fetch API middleware
						wp.apiFetch.use((options, next) => {
						
							// Check if using this payment method
							if(usingThisPaymentMethod === true) {
							
								// Check if options doesn't include headers
								if("headers" in options === false || typeof options.headers !== "object" || options.headers === null) {
								
									// Set options headers
									options.headers = {};
								}
								
								// Set requested with header
								options.headers["X-Requested-With"] = "XMLHttpRequest";
								
								// Check if request is to the store API
								if("path" in options === true && typeof options.path === "string" && /^\/wc\/store\//u.test(options.path) === true) {
								
									// Add payment method to request's path
									options.path += ((options.path.indexOf("?") === -1) ? "?" : "&") + $.param({
									
										// Payment method
										[MwcPayWooCommerceExtension_base_script_parameters.gateway_id + "_payment_method"]: MwcPayWooCommerceExtension_base_script_parameters.gateway_id
									});
								}
							}
							
							// Return perform request
							return next(options);
						});
						
						// Check if using this payment method
						if(usingThisPaymentMethod === true) {
						
							// Show checkout total
							$("span#MwcPayWooCommerceExtension_checkout_total").removeClass("MwcPayWooCommerceExtension_checkout_hide");
						}
						
						// Otherwise
						else {
						
							// Hide checkout total
							$("span#MwcPayWooCommerceExtension_checkout_total").addClass("MwcPayWooCommerceExtension_checkout_hide");
						}
						
						// Check if payment method has a discount or surcharge
						if(MwcPayWooCommerceExtension_checkout_script_parameters.has_discount_or_surcharge === true) {
						
							// Try
							try {
							
								// Refresh cart
								await wp.data.dispatch(wc.wcBlocksData.CART_STORE_KEY).receiveCartContents(await wp.apiFetch({
								
									// Path
									path: "/wc/store/v1/cart",
									
									// Method
									method: "GET",
									
									// Cache
									cache: "no-store"
								}));
							}
							
							// Catch errors
							catch(error) {
							
							}
						}
					});
				}
			}
		}
		
		// Price in currency class
		const PRICE_CHANGED_EVENT = "MwcPayWooCommerceExtension_checkout_price_changed_event";
		class PriceInCurrency extends React.Component {
		
			// Constructor
			constructor(props) {
			
				// Delegate constructor
				super(props);
				
				// Set currency
				this.PriceInCurrency_currency = wc.priceFormat.getCurrency({
										
					// Code
					code: MwcPayWooCommerceExtension_base_script_parameters.currency_id,
					
					// Symbol
					symbol: MwcPayWooCommerceExtension_base_script_parameters.currency_symbol,
					
					// Minor unit
					minorUnit: MwcPayWooCommerceExtension_base_script_parameters.currency_decimals,
					
					// Prefix
					prefix: "",
					
					// Suffix
					suffix: " " + MwcPayWooCommerceExtension_base_script_parameters.currency_symbol
				});
				
				// Set on price changed
				this.PriceInCurrency_onPriceChanged = (() => {
				
					// Force update
					this.forceUpdate();
					
				}).bind(this);
			}
			
			// Render
			render() {
			
				// Check if price and currency exchange rate exist
				if(MwcPayWooCommerceExtension_checkout_script_parameters.price !== null && MwcPayWooCommerceExtension_checkout_script_parameters.currency_exchange_rate !== null) {
				
					// Check if exchange rate is valid and greater than zero
					const exchangeRate = new BigNumber(MwcPayWooCommerceExtension_checkout_script_parameters.currency_exchange_rate);
					
					if(exchangeRate.isNaN() === false && exchangeRate.isGreaterThan(0) === true) {
					
						// Get exchange rate number of decimal digits
						const exchangeRateNumberOfDecimalsDigits = MwcPayWooCommerceExtension_checkout_script_parameters.currency_exchange_rate.match(/^[+-]?\d+\.(\d+)(?:e|$)/ui);
						
						// Apply exchange rate to value to get the value in USD
						const valueInUsd = new BigNumber(this.props.value).shiftedBy(-1 * this.props.currency.minorUnit).dividedBy(exchangeRate).decimalPlaces(this.props.currency.minorUnit + ((exchangeRateNumberOfDecimalsDigits === null) ? 0 : exchangeRateNumberOfDecimalsDigits[1].length), BigNumber.ROUND_UP);
						
						// Return outer container
						return React.createElement("span", {
					
							// ID
							id: "MwcPayWooCommerceExtension_checkout_total",
							
							// Class name
							className: "MwcPayWooCommerceExtension_checkout_hide"
						}, [
						
							// Space
							" ",
							
							// Inner container
							React.createElement("bdi", {}, [
							
								// Open parenthesis
								React.createElement("span", {
								
									// Dangerously set inner HTML
									dangerouslySetInnerHTML: {
									
										// HTML
										__html: "(≈&zwj;"
									}
								}),
								
								// Formatted monetary amount
								React.createElement(wc.blocksComponents.FormattedMonetaryAmount, {
								
									// Class name
									className: "MwcPayWooCommerceExtension_checkout_total_amount",
									
									// Currency
									currency: this.PriceInCurrency_currency,
									
									// Value
									value: valueInUsd.dividedBy(MwcPayWooCommerceExtension_checkout_script_parameters.price).toFixed(this.PriceInCurrency_currency.minorUnit, BigNumber.ROUND_UP).replace(".", "")
								}),
								
								// Close parenthesis
								")"
							])
						]);
					}
				}
				
				// Return null
				return null;
			}
			
			// Component did mount
			componentDidMount() {
			
				// jQuery ready
				jQuery(($) => {
				
					// Add price changed event
					$(document).on(PRICE_CHANGED_EVENT, this.PriceInCurrency_onPriceChanged);
					
					// Check if using this payment method
					if($("#radio-control-wc-payment-method-options-mwc_pay").is(":checked") === true) {
					
						// Show checkout total
						$("span#MwcPayWooCommerceExtension_checkout_total").removeClass("MwcPayWooCommerceExtension_checkout_hide");
					}
					
					// Otherwise
					else {
					
						// Hide checkout total
						$("span#MwcPayWooCommerceExtension_checkout_total").addClass("MwcPayWooCommerceExtension_checkout_hide");
					}
				});
			}
			
			// Component will unmount
			componentWillUnmount() {
			
				// jQuery ready
				jQuery(($) => {
				
					// Remove price changed event
					$(document).off(PRICE_CHANGED_EVENT, this.PriceInCurrency_onPriceChanged);
				});
			}
		}
		
		// Override React's create element
		const originalCreateElement = React.createElement;
		React.createElement = function(type, props) {
		
			// Check if properties has a class name
			if(typeof props === "object" && props !== null && "className" in props === true && typeof props.className === "string") {
			
				// Check if creating a checkout
				if(/(?:^| )wc-block-checkout(?:$| )/u.test(props.className) === true) {
				
					// Return checkout container
					return originalCreateElement(CheckoutContainer, {}, originalCreateElement(...arguments));
				}
				
				// Otherwise check if currency isn't this currency
				else if(wc.priceFormat.getCurrency().code !== MwcPayWooCommerceExtension_base_script_parameters.currency_id) {
				
					// Check if creating a totals footer item
					if(/[a-z]-totals-footer-item(?:$| )/u.test(props.className) === true) {
				
						// Check if value and currency exist and are valid
						if("value" in props === true && typeof props.value === "number" && Number.isFinite(props.value) === true && "currency" in props === true && typeof props.currency === "object" && props.currency !== null && "minorUnit" in props.currency === true && typeof props.currency.minorUnit === "number") {
						
							// Set props's value
							props.value = originalCreateElement("span", {}, [
							
								// Formatted monetary amount
								originalCreateElement(wc.blocksComponents.FormattedMonetaryAmount, {
								
									// Currency
									currency: props.currency,
									
									// Value
									value: props.value
								}),
								
								// Price in currency
								originalCreateElement(PriceInCurrency, {
								
									// Currency
									currency: props.currency,
									
									// Value
									value: props.value
								})
							]);
						}
					}
					
					// Otherwise check if creating a price in currency amount
					else if(/(?:^| )MwcPayWooCommerceExtension_checkout_total_amount(?:$| )/u.test(props.className) === true) {
					
						// Set fixed decimal scale to remove trailing zeros
						props.fixedDecimalScale = false;
					}
				}
			}
			
			// Return creating element
			return originalCreateElement(...arguments);
		};
		
		// jQuery ready
		jQuery(($) => {
		
			// MWC Pay WooCommerce extension checkout class
			class MwcPayWooCommerceExtensionCheckout {
			
				// Constructor
				constructor() {
				
					// Get modal
					this.modal = $("div#MwcPayWooCommerceExtension_checkout_modal");
					
					// Get modal's cancel button
					this.cancelButton = this.modal.find("button");
					
					// Set update status timeout to null
					this.updateStatusTimeout = null;
					
					// Set update instructions timeout to null
					this.updateInstructionsTimeout = null;
					
					// Set bypass unload interrupt to false
					this.bypassUnloadInterrupt = false;
					
					// Set update price timeout to null
					this.updatePriceTimeout = null;
					
					// Intercept checkout responses
					this.interceptCheckoutResponses();
					
					// Update price
					this.updatePrice();
					
					// Modal amount transition end event
					this.modal.on("transitionend", (event) => {
					
						// Stop propagation
						event.stopPropagation();
					
					// Modal amount click event
					}).on("click", "> div > p > bdi:not(.MwcPayWooCommerceExtension_checkout_ignore) > span", (event) => {
					
						// Get amount
						const amount = $(event.currentTarget);
						
						// Check if amount isn't already being copied
						if(amount.hasClass("MwcPayWooCommerceExtension_checkout_copied") === false) {
						
							// Set that amount is being copied
							amount.addClass("MwcPayWooCommerceExtension_checkout_copied");
							
							// Set timeout
							setTimeout(async () => {
							
								// Try
								try {
								
									// Write amount to clipboard
									await navigator.clipboard.writeText(this.amount);
								}
								
								// Catch errors
								catch(error) {
								
								}
								
								// Finally
								finally {
								
									// Set that amount isn't being copied
									amount.removeClass("MwcPayWooCommerceExtension_checkout_copied");
								}
								
							}, MwcPayWooCommerceExtensionCheckout.WRITE_TO_CLIPBOARD_DELAY_MILLISECONDS);
						}
					
					// Modal URL click event
					}).on("click", "> div > p > bdi:not(.MwcPayWooCommerceExtension_checkout_ignore) > a", (event) => {
					
						// Get URL
						const url = $(event.currentTarget);
						
						// Get URL's link
						const link = url.attr("href");
					
						// Check if MWC Wallet extension is installed and the event isn't recursive
						if(typeof MwcWallet !== "undefined" && event.originalEvent.isTrusted !== false) {
						
							// Prevent default
							event.preventDefault();
							
							// Start transaction with the MWC Wallet extension and catch errors
							MwcWallet.startTransaction(MwcWallet.MWC_WALLET_TYPE, MwcWallet.MAINNET_NETWORK_TYPE, link, this.amount).catch((error) => {
							
								// Trigger modal URL click event
								event.originalEvent.target.click();
							});
						}
						
						// Otherwise
						else {
						
							// Add protocol to URL's link
							url.attr("href", MwcPayWooCommerceExtension_base_script_parameters.url_protocol + link);
							
							// Set timeout
							setTimeout(() => {
							
								// Remove protocol from URL's link
								url.attr("href", link);
							}, 0);
						}
					});
					
					// Modal cancel button click event
					this.cancelButton.on("click", () => {
					
						// Cancel order
						fetch(this.cancelApi);
						
						// Hide payment info
						this.hidePaymentInfo(wp.escapeHtml.escapeEditableHTML(wp.i18n.__("Order was cancelled.", "mwc-pay-woocommerce-extension")));
					});
					
					// Window before unload event
					$(window).on("beforeunload", (event) => {
					
						// Check if modal is shown and not bypassing unload interrupt
						if(this.modal.hasClass("MwcPayWooCommerceExtension_checkout_hide") === false && this.bypassUnloadInterrupt === false) {
						
							// Prevent default to show interrupt prompt
							event.preventDefault();
							
							// Set return value and return true to show interrupt prompt
							return event.originalEvent.returnValue = true;
						}
					
					// Window unload event
					}).on("unload", () => {
					
						// Check if modal is shown and not bypassing unload interrupt
						if(this.modal.hasClass("MwcPayWooCommerceExtension_checkout_hide") === false && this.bypassUnloadInterrupt === false) {
						
							// Trigger click on modal's cancel button
							this.cancelButton.trigger("click");
						}
						
						// Otherwise
						else {
						
							// Hide payment info
							this.hidePaymentInfo();
						}
					});
					
					// Payment this method change event
					let usingThisPaymentMethod = $("#payment_method_mwc_pay").is(":checked") === true;
					$("#order_review").on("change", "input[name=\"payment_method\"]", () => {
					
						// Get new using this payment method
						const newUsingPaymentMethod = $("#payment_method_mwc_pay").is(":checked") === true;
						
						// Check if using this payment method changed
						if(newUsingPaymentMethod !== usingThisPaymentMethod) {
						
							// Update using this payment method
							usingThisPaymentMethod = newUsingPaymentMethod;
							
							// Check if using this payment method
							if(usingThisPaymentMethod === true) {
							
								// Show checkout total
								$("span#MwcPayWooCommerceExtension_checkout_total").removeClass("MwcPayWooCommerceExtension_checkout_hide");
							}
							
							// Otherwise
							else {
							
								// Hide checkout total
								$("span#MwcPayWooCommerceExtension_checkout_total").addClass("MwcPayWooCommerceExtension_checkout_hide");
							}
							
							// Check if payment method has a discount or surcharge
							if(MwcPayWooCommerceExtension_checkout_script_parameters.has_discount_or_surcharge === true) {
							
								// Trigger update checkout event
								$(document.body).trigger("update_checkout");
							}
						}
					});
					
					// Updated checkout event
					$(document.body).on("updated_checkout", () => {
					
						// Check if using this payment method
						if(usingThisPaymentMethod === true) {
						
							// Show checkout total
							$("span#MwcPayWooCommerceExtension_checkout_total").removeClass("MwcPayWooCommerceExtension_checkout_hide");
						}
						
						// Otherwise
						else {
						
							// Hide checkout total
							$("span#MwcPayWooCommerceExtension_checkout_total").addClass("MwcPayWooCommerceExtension_checkout_hide");
						}
					});
					
					// Check if payment method is available
					if($("#payment_method_mwc_pay").length !== 0) {
					
						// Check if using this payment method
						if(usingThisPaymentMethod === true) {
						
							// Show checkout total
							$("span#MwcPayWooCommerceExtension_checkout_total").removeClass("MwcPayWooCommerceExtension_checkout_hide");
						}
						
						// Otherwise
						else {
						
							// Hide checkout total
							$("span#MwcPayWooCommerceExtension_checkout_total").addClass("MwcPayWooCommerceExtension_checkout_hide");
						}
					}
					
					// Check if payment method has a discount or surcharge
					if(MwcPayWooCommerceExtension_checkout_script_parameters.has_discount_or_surcharge === true) {
					
						// Trigger update checkout event
						$(document.body).trigger("update_checkout");
					}
				}
				
				// Intercept checkout responses
				interceptCheckoutResponses() {
				
					// Set jQuery AJAX prefilter
					$.ajaxPrefilter((options, originalOptions, jqXhr) => {
					
						// Check if request is to place a WooCommerce order
						if(typeof wc_checkout_params !== "undefined" && "url" in options === true && options.url === wc_checkout_params.checkout_url && "data" in options === true) {
						
							// Check if request uses this payment method
							const data = new URLSearchParams(options.data);
							if(data.has("payment_method") === true && data.get("payment_method") === MwcPayWooCommerceExtension_base_script_parameters.gateway_id) {
							
								// Save request's success callback
								this.successCallback = options.success;
								
								// Change request's success callback
								options.success = (result) => {
								
									// Check if result isn't successful or doesn't have a way to get its status
									if(result.result !== "success" || "status_api" in result === false) {
									
										// Perform success callback
										this.successCallback(result);
									}
									
									// Otherwise
									else {
									
										// Set amount
										this.amount = result.amount;
										
										// Set timeout
										this.timeout = parseInt(result.timeout, MwcPayWooCommerceExtensionCheckout.DECIMAL_NUMBER_BASE);
										
										// Set cancel API
										this.cancelApi = result.cancel_api;
										
										// Show payment info
										this.showPaymentInfo(result.url, result.amount, result.required_number_of_block_confirmations, result.recipient_payment_proof_address, result.price_in_currency, result.price_in_mimblewimble_coin, result.payment_method_title, result.status_api, result.redirect);
									}
								};
							}
						}
					});
					
					// Use fetch API middleware
					wp.apiFetch.use((options, next) => {
					
						// Check if request is placing a WooCommerce order using this payment method
						if("method" in options === true && options.method === "POST" && "path" in options === true && typeof options.path === "string" && /^\/wc\/store\/(?:v\d+\/)?checkout(?:$|\/|\\|\?)/u.test(options.path.replace(/[\/\\]+$/u, "")) === true && "data" in options === true && typeof options.data === "object" && options.data !== null && "payment_method" in options.data === true && options.data.payment_method === MwcPayWooCommerceExtension_base_script_parameters.gateway_id) {
					
							// Check if options doesn't include headers
							if("headers" in options === false || typeof options.headers !== "object" || options.headers === null) {
							
								// Set options headers
								options.headers = {};
							}
							
							// Set requested with header
							options.headers["X-Requested-With"] = "XMLHttpRequest";
							
							// Return promise
							return new Promise((resolve, reject) => {
							
								// Return performing request
								return next(options).then((response) => {
								
									// Return getting response as json
									return response.clone().json().then((jsonResponse) => {
									
										// Get result
										const result = jsonResponse.payment_result.payment_details.reduce((currentResult, value) => {
										
											// Set value in current result
											currentResult[value.key] = value.value;
											
											// Return current result
											return currentResult;
										}, {});
										
										// Check if result isn't successful or doesn't have a way to get its status
										if(result.result !== "success" || "status_api" in result === false) {
										
											// Resolve response
											resolve(response);
										}
										
										// Otherwise
										else {
										
											// Set success callback
											this.successCallback = (values) => {
											
												// Check if value is success
												if(values.result === "success") {
												
													// Resolve response
													resolve(response);
												}
												
												// Otherwise
												else {
												
													// Reject
													reject(new Response(JSON.stringify({
														
														// Message
														message: $(values.messages).html()
													})));
												}
											};
											
											// Set amount
											this.amount = result.amount;
											
											// Set timeout
											this.timeout = parseInt(result.timeout, MwcPayWooCommerceExtensionCheckout.DECIMAL_NUMBER_BASE);
											
											// Set cancel API
											this.cancelApi = result.cancel_api;
											
											// Show payment info
											this.showPaymentInfo(result.url, result.amount, result.required_number_of_block_confirmations, result.recipient_payment_proof_address, result.price_in_currency, result.price_in_mimblewimble_coin, result.payment_method_title, result.status_api, result.redirect);
										}
										
									// Catch errors
									}).catch((error) => {
									
										// Resolve response
										resolve(response);
									});
								
								// Catch errors
								}).catch((error) => {
								
									// Reject error
									reject(error);
								});
							});
						}
						
						// Otherwise
						else {
						
							// Return perform request
							return next(options);
						}
					});
				}
				
				// Update price
				updatePrice(immediatley = false) {
				
					// Clear update price timeout
					clearTimeout(this.updatePriceTimeout);
					
					// Set update price timeout
					this.updatePriceTimeout = setTimeout(async () => {
					
						// Try
						try {
						
							// Check if currency isn't this currency, this payment method is available, and modal isn't shown
							if(wc.priceFormat.getCurrency().code !== MwcPayWooCommerceExtension_base_script_parameters.currency_id && $("#payment_method_mwc_pay, #radio-control-wc-payment-method-options-mwc_pay").length !== 0 && this.modal.hasClass("MwcPayWooCommerceExtension_checkout_hide") === true) {
							
								// Get price
								const price = await (await fetch(MwcPayWooCommerceExtension_checkout_script_parameters.get_price_api)).json();
								
								// Check if price exists and it changed or currency exchange rate exists and it changed
								if((price.price !== null && price.price !== MwcPayWooCommerceExtension_checkout_script_parameters.price) || (price.currency_exchange_rate !== null && price.currency_exchange_rate !== MwcPayWooCommerceExtension_checkout_script_parameters.currency_exchange_rate)) {
								
									// Check if price exists
									if(price.price !== null) {
									
										// Update price in parameters
										MwcPayWooCommerceExtension_checkout_script_parameters.price = price.price;
									}
									
									// Check if currency exchange rate exists
									if(price.currency_exchange_rate !== null) {
									
										// Update currency exchange rate in parameters
										MwcPayWooCommerceExtension_checkout_script_parameters.currency_exchange_rate = price.currency_exchange_rate;
									}
									
									// Trigger update checkout event
									$(document.body).trigger("update_checkout");
									
									// Trigger price changed event
									$(document).trigger(PRICE_CHANGED_EVENT);
								}
							}
						}
						
						// Catch errors
						catch(error) {
						
						}
						
						// Finally
						finally {
						
							// Update price
							this.updatePrice();
						}
						
					}, (immediatley === true) ? 0 : MwcPayWooCommerceExtensionCheckout.UPDATE_PRICE_INTERVAL_MILLISECONDS);
				}
				
				// Show payment info
				showPaymentInfo(url, amount, requiredNumberOfBlockConfirmations, recipientPaymentProofAddress, priceInCurrency, priceInMimbleWimbleCoin, paymentMethodTitle, statusApi, redirect) {
						
					// Set modal's z-index
					this.modal.css("z-index", $.blockUI.defaults.baseZ);
					
					// Set modal's background color to the body's and return it
					const backgroundColor = this.modal.find("> div:last-of-type").css("background-color", $(document.body).css("background-color")).css("background-color");
					
					// Check if background color is dark
					if(MwcPayWooCommerceExtensionCheckout.colorIsDark(backgroundColor) === true) {
					
						// Invert modal's colors
						this.modal.addClass("MwcPayWooCommerceExtension_checkout_invert");
					}
					
					// Otherwise
					else {
					
						// Don't invert modal's colors
						this.modal.removeClass("MwcPayWooCommerceExtension_checkout_invert");
					}
					
					// Clear modal's instructions
					this.modal.find("> div > p:first-of-type").empty();
					
					// Set modal's title
					this.modal.find("h2").text(paymentMethodTitle);
					
					// Try
					try {
					
						// Initialize QR code
						qrcode.stringToBytes = qrcode.stringToBytesFuncs["UTF-8"];
						const qrCode = qrcode(0, MwcPayWooCommerceExtensionCheckout.QR_CODE_ERROR_CORRECTION_LEVEL);
						
						// Add recipient address and amount to QR code
						qrCode.addData(JSON.stringify({
						
							// Recipient address
							"Recipient Address": url,
							
							// Amount
							"Amount": amount
							
						}), "Byte");
						
						// Create QR code
						qrCode.make();
						
						// Set modal's QR code
						this.modal.find("img").replaceWith(qrCode.createImgTag());
						
						// Set modal's QR code alternative
						this.modal.find("img").attr("alt", wp.i18n.__("Payment QR code", "mwc-pay-woocommerce-extension"));
						
						// Show modal's QR code
						this.modal.addClass("MwcPayWooCommerceExtension_checkout_qrcode");
					}
					
					// Catch errors
					catch(error) {
					
						// Hide modal's QR code
						this.modal.removeClass("MwcPayWooCommerceExtension_checkout_qrcode");
					}
					
					// Set modal's payment proof
					this.modal.find("p:last-of-type").html(wp.i18n.sprintf(wp.escapeHtml.escapeEditableHTML(wp.i18n.__("The transaction's recipient payment proof address for this order is %s", "mwc-pay-woocommerce-extension")), "<span><bdi>" + wp.escapeHtml.escapeEditableHTML(recipientPaymentProofAddress) + "</bdi>.</span>"));
					
					// Enable modal's cancel button
					this.cancelButton.prop("disabled", false);
					
					// Update modal's instructions
					this.updateInstructions(url, requiredNumberOfBlockConfirmations, priceInCurrency, priceInMimbleWimbleCoin);
					
					// Show modal
					this.modal.removeClass("MwcPayWooCommerceExtension_checkout_hide");
					
					// Update status
					this.updateStatus(statusApi, redirect);
				}
				
				// Hide payment info
				hidePaymentInfo(message = null) {
				
					// Disable modal's cancel button
					this.cancelButton.prop("disabled", true);
				
					// Trigger cancelled event
					this.modal.trigger(MwcPayWooCommerceExtensionCheckout.CANCELLED_EVENT);
					
					// Clear update status timeout
					clearTimeout(this.updateStatusTimeout);
					
					// Clear update instructions timeout
					clearTimeout(this.updateInstructionsTimeout);
					
					// Check if message exists
					if(message !== null) {
					
						// Perform success callback
						this.successCallback({
						
							// Result
							result: "failure",
							
							// Messages
							messages: "<div class=\"woocommerce-error\">" + message + "</div>",
							
							// Refresh
							refresh: false,
							
							// Reload
							reload: false
						});
					}
					
					// Hide modal
					this.modal.addClass("MwcPayWooCommerceExtension_checkout_hide");
					
					// Check if message exists
					if(message !== null) {
					
						// Update price immediatley
						this.updatePrice(true);
					}
				}
				
				// Update instructions
				updateInstructions(url, requiredNumberOfBlockConfirmations, priceInCurrency, priceInMimbleWimbleCoin) {
				
					// Get modal's instructions
					const instructions = this.modal.find("> div > p:first-of-type");
					
					// Check if modal's instructions are empty or will change other than the timeout
					if(instructions.is(":empty") === true || wp.i18n._n("Send %1$s%2$s to %3$s in the next %4$s second to complete your order.", "Send %1$s%2$s to %3$s in the next %4$s seconds to complete your order.", this.timeout, "mwc-pay-woocommerce-extension") !== wp.i18n._n("Send %1$s%2$s to %3$s in the next %4$s second to complete your order.", "Send %1$s%2$s to %3$s in the next %4$s seconds to complete your order.", this.timeout + 1, "mwc-pay-woocommerce-extension")) {
					
						// Update modal's instructions
						instructions.html(wp.i18n.sprintf(wp.escapeHtml.escapeEditableHTML(wp.i18n._n("Send %1$s%2$s to %3$s in the next %4$s second to complete your order.", "Send %1$s%2$s to %3$s in the next %4$s seconds to complete your order.", this.timeout, "mwc-pay-woocommerce-extension")), priceInMimbleWimbleCoin, (wc.priceFormat.getCurrency().code !== MwcPayWooCommerceExtension_base_script_parameters.currency_id) ? " <bdi class=\"MwcPayWooCommerceExtension_checkout_ignore\">(≈&zwj;" + priceInCurrency + ")</bdi>" : "", "<bdi><a href=\"" + wp.escapeHtml.escapeQuotationMark(url) + "\" aria-label=\"" + wp.escapeHtml.escapeQuotationMark(wp.i18n.__("Open payment URL", "mwc-pay-woocommerce-extension")) + "\" target=\"_blank\" rel=\"nofollow noopener noreferrer\">" + wp.escapeHtml.escapeEditableHTML(url) + "</a></bdi>", "<bdi class=\"MwcPayWooCommerceExtension_checkout_timeout\">" + wp.escapeHtml.escapeEditableHTML(MwcPayWooCommerceExtensionCheckout.addThousandSeparator(this.timeout.toFixed())) + "</bdi>") + " " + wp.i18n.sprintf(wp.escapeHtml.escapeEditableHTML(wp.i18n._n("This order will start processing once its payment achieves %1$s block confirmation %2$s minute%3$s.", "This order will start processing once its payment achieves %1$s block confirmations %2$s minutes%3$s.", parseInt(requiredNumberOfBlockConfirmations, MwcPayWooCommerceExtensionCheckout.DECIMAL_NUMBER_BASE), "mwc-pay-woocommerce-extension")), "<bdi>" + wp.escapeHtml.escapeEditableHTML(MwcPayWooCommerceExtensionCheckout.addThousandSeparator(requiredNumberOfBlockConfirmations)) + "</bdi>", "<bdi>(≈&zwj;" + wp.escapeHtml.escapeEditableHTML(MwcPayWooCommerceExtensionCheckout.addThousandSeparator(requiredNumberOfBlockConfirmations)) + "</bdi>", "<bdi>)</bdi>"));
					}
					
					// Otherwise
					else {
					
						// Update modal's instructions's timeout
						instructions.find("> bdi.MwcPayWooCommerceExtension_checkout_timeout").text(MwcPayWooCommerceExtensionCheckout.addThousandSeparator(this.timeout.toFixed()));
					}
					
					// Check if timeout isn't done
					if(this.timeout > 1) {
					
						// Set update instructions timeout
						this.updateInstructionsTimeout = setTimeout(() => {
						
							// Decrement timeout
							--this.timeout;
						
							// Update instructions
							this.updateInstructions(url, requiredNumberOfBlockConfirmations, priceInCurrency, priceInMimbleWimbleCoin);
						
						}, 1 * MwcPayWooCommerceExtensionCheckout.MILLISECONDS_IN_A_SECOND);
					}
				}
				
				// Update status
				async updateStatus(statusApi, redirect) {
				
					// Cancelled event
					let ignoreResult = false;
					this.modal.one(MwcPayWooCommerceExtensionCheckout.CANCELLED_EVENT, () => {
					
						// Set ignore result to true
						ignoreResult = true;
					});
					
					// Try
					let tryAgain = true;
					try {
					
						// Get status
						const status = await (await fetch(statusApi)).json();
						
						// Check if not ignoring result
						if(ignoreResult === false) {
						
							// Check if status is paid
							if(status.paid === true) {
							
								// Set try again to false
								tryAgain = false;
								
								// Disable modal's cancel button
								this.cancelButton.prop("disabled", true);
								
								// Set bypass unload interrupt to true
								this.bypassUnloadInterrupt = true;
							
								// Perform success callback
								this.successCallback({
								
									// Result
									result: "success",
									
									// Redirect
									redirect
								});
							}
							
							// Otherwise check if status is expired
							else if(status.expired === true) {
							
								// Set try again to false
								tryAgain = false;
								
								// Hide payment info
								this.hidePaymentInfo(wp.escapeHtml.escapeEditableHTML(wp.i18n.__("Order expired.", "mwc-pay-woocommerce-extension")));
							}
							
							// Otherwise check if status is cancelled
							else if(status.cancelled === true) {
							
								// Set try again to false
								tryAgain = false;
								
								// Hide payment info
								this.hidePaymentInfo(wp.escapeHtml.escapeEditableHTML(wp.i18n.__("Order was cancelled.", "mwc-pay-woocommerce-extension")));
							}
						}
					}
					
					// Catch errors
					catch(error) {
					
					}
					
					// Finally
					finally {
					
						// Check if not ignoring result
						if(ignoreResult === false) {
						
							// Turn off cancelled event
							this.modal.off(MwcPayWooCommerceExtensionCheckout.CANCELLED_EVENT);
							
							// Check if trying again
							if(tryAgain === true) {
						
								// Set update status timeout
								this.updateStatusTimeout = setTimeout(() => {
								
									// Update status
									this.updateStatus(statusApi, redirect);
									
								}, Math.min(MwcPayWooCommerceExtensionCheckout.UPDATE_STATUS_INTERVAL_MILLISECONDS, this.timeout * MwcPayWooCommerceExtensionCheckout.MILLISECONDS_IN_A_SECOND));
							}
						}
					}
				}
				
				// Color is dark
				static colorIsDark(color) {
				
					// Get color's components
					const components = color.match(/^rgba?\((\d+), *(\d+), *(\d+)/u);
					const red = parseInt(components[1], MwcPayWooCommerceExtensionCheckout.DECIMAL_NUMBER_BASE);
					const green = parseInt(components[2], MwcPayWooCommerceExtensionCheckout.DECIMAL_NUMBER_BASE);
					const blue = parseInt(components[3], MwcPayWooCommerceExtensionCheckout.DECIMAL_NUMBER_BASE);
					
					// Get color's brightness
					const brightness = Math.sqrt(0.299 * Math.pow(red, 2) + 0.587 * Math.pow(green, 2) + 0.114 * Math.pow(blue, 2));
					
					// Return if color is dark
					return brightness <= MwcPayWooCommerceExtensionCheckout.UINT8_MAX / 2;
				}
				
				// Add thousand separator
				static addThousandSeparator(number) {
				
					// Return number with thousand separator
					return number.replace(/\B(?=(\d{3})+(?!\d))/ug, wc.priceFormat.getCurrency().thousandSeparator);
				}
				
				// QR code error correction level
				static get QR_CODE_ERROR_CORRECTION_LEVEL() {
				
					// Return QR code error correction level
					return "L";
				}
				
				// Write to clipboard delay milliseconds
				static get WRITE_TO_CLIPBOARD_DELAY_MILLISECONDS() {
				
					// Return write to clipboard delay milliseconds
					return 200;
				}
				
				// Cancelled event
				static get CANCELLED_EVENT() {
				
					// Return cancelled event
					return "MwcPayWooCommerceExtension_checkout_cancelled_event";
				}
				
				// Milliseconds in a second
				static get MILLISECONDS_IN_A_SECOND() {
				
					// Return milliseconds in a second
					return 1000;
				}
				
				// Seconds in a minute
				static get SECONDS_IN_A_MINUTE() {
				
					// Return seconds in a minute
					return 60;
				}
				
				// Update status interval milliseconds
				static get UPDATE_STATUS_INTERVAL_MILLISECONDS() {
				
					// Return update status interval seconds
					return 10 * MwcPayWooCommerceExtensionCheckout.MILLISECONDS_IN_A_SECOND;
				}
				
				// Update price interval milliseconds
				static get UPDATE_PRICE_INTERVAL_MILLISECONDS() {
				
					// Return update price interval milliseconds
					return 10 * MwcPayWooCommerceExtensionCheckout.SECONDS_IN_A_MINUTE * MwcPayWooCommerceExtensionCheckout.MILLISECONDS_IN_A_SECOND;
				}
				
				// Decimal number base
				static get DECIMAL_NUMBER_BASE() {
				
					// Return decimal number base
					return 10;
				}
				
				// Uint8 max
				static get UINT8_MAX() {
				
					// Return uint8 max
					return 255;
				}
			}
			
			// Create MWC Pay WooCommerce extension checkout
			const mwcPayWooCommerceExtensionCheckout = new MwcPayWooCommerceExtensionCheckout();
		});
	}
})();
