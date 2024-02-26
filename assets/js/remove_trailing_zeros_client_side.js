// Main function
(() => {

	// Use strict
	"use strict";
	
	// Override React's create element
	const originalCreateElement = React.createElement;
	React.createElement = function(type, props) {
	
		// Check if creating a formatted money amount
		if(typeof props === "object" && props !== null && "className" in props === true && typeof props.className === "string" && /[a-z]-formatted-money-amount(?:$| )/u.test(props.className) === true) {
		
			// Set fixed decimal scale to remove trailing zeros
			props.fixedDecimalScale = false;
		}
		
		// Return creating element
		return originalCreateElement(...arguments);
	};
})();
