// Main function
(() => {

	// Use strict
	"use strict";
	
	// Register block type
	wp.blocks.registerBlockType("mwc-pay-woocommerce-extension/mimblewimble-coin-accepted-here-badge", {
	
		// Edit
		edit: () => wp.element.createElement("div", wp.blockEditor.useInnerBlocksProps(wp.blockEditor.useBlockProps({
			
			// Reference
			ref: (element) => {
			
				// Check if element exists
				if(element !== null) {
				
					// Make element's backround and background color transparent
					element.style.setProperty("background", "transparent", "important");
					element.style.setProperty("background-color", "transparent", "important");
				}
			}
			
		})), wp.element.createElement("div", {
		
			// Class name
			className: wp.blockEditor.useBlockProps().className,
			
			// Style
			style: wp.blockEditor.useBlockProps().style
			
		}, [
	
			// Image
			wp.element.createElement("img", {
		
				// Source
				src: MwcPayWooCommerceExtension_blocks_script_parameters.blocks_path + "mimblewimble_coin_accepted_here_badge/mimblewimble_coin_logo.svg",
				
				// Alternative
				alt: wp.i18n.__("MimbleWimble Coin logo", "mwc-pay-woocommerce-extension")
			}),
			
			// Text
			wp.element.createElement("p", null, [
			
				// First line
				wp.element.createElement("span", null, wp.i18n.__("MimbleWimble Coin", "mwc-pay-woocommerce-extension")),
				
				// Second line
				wp.element.createElement("span", null, wp.i18n.__("Accepted here", "mwc-pay-woocommerce-extension"))
			])
		]))
	});
})();
