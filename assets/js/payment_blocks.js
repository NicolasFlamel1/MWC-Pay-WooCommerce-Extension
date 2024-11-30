// Main function
(() => {

	// Use strict
	"use strict";

	// Check if MWC Pay WooCommerce extension base script parameters exists
	if(typeof MwcPayWooCommerceExtension_base_script_parameters !== "undefined") {

		// Check if getting settings was successful
		const settings = wc.wcSettings.getSetting(MwcPayWooCommerceExtension_base_script_parameters.gateway_id + "_data");
		if(settings !== false) {
		
			// Register payment method
			wc.wcBlocksRegistry.registerPaymentMethod({

				// Name
				name: MwcPayWooCommerceExtension_base_script_parameters.gateway_id,
				
				// Label
				label: wp.element.createElement((props) => wp.element.createElement(props.components.PaymentMethodLabel, {
				
					// text
					text: (settings.gateway_display_format === "title" || settings.gateway_display_format === "title_and_icon") ? settings.gateway_title : "",
					
					// Icon
					icon: (settings.gateway_display_format === "icon" || settings.gateway_display_format === "title_and_icon") ? wp.element.createElement("img", {
					
						// Source
						src: settings.gateway_icon,
						
						// Alternative
						alt: settings.gateway_title,
						
						// Style
						style: (settings.gateway_display_format === "icon") ? {
						
							// Height
							height: "auto",
							
							// Max height
							"max-height": "none",
							
							// Margin right
							"margin-right": "0",
							
							// Margin bottom
							"margin-bottom": "-8px",
							
							// Display
							display: "initial"
						} : {
						
							// Margin bottom
							"margin-bottom": "-2px"
						}
					}) : ""
				})),
				
				// Aria label
				ariaLabel: settings.gateway_title,
				
				// Content
				content: wp.element.createElement(wp.element.RawHTML, null, settings.gateway_description),
				
				// Edit
				edit: wp.element.createElement(wp.element.RawHTML, null, settings.gateway_description),
				
				// Can make payment
				canMakePayment: () => true,
				
				// Supports
				supports: {
				
					// Features
					features: settings.gateway_supports
				}
			});
		}
	}
})();
