<?php


// Enforce strict types
declare(strict_types=1);


// Check if file is accessed directly
if(defined("ABSPATH") === FALSE) {

	// Exit
	exit;
}


?>

<div id="MwcPayWooCommerceExtension_checkout_modal" class="MwcPayWooCommerceExtension_checkout_hide wp-block-group alignfull has-global-padding is-layout-flex wp-block-group-is-layout-flex is-vertical is-content-justification-center">

	<div class="alignfull has-black-background-color has-background"></div>
	
	<div class="wp-block-group alignwide has-base-background-color has-background has-contrast-2-border-color has-border has-global-padding is-layout-constrained wp-block-group-is-layout-constrained">
	
		<div aria-hidden="true" class="wp-block-spacer"></div>
		
		<h2 class="wp-block-heading has-text-align-center has-x-large-font-size"></h2>
		
		<p class="has-text-align-center"></p>
		
		<img>
		
		<p class="has-text-align-center"></p>
		
		<div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex is-vertical is-content-justification-center">
			
			<div class="woocommerce wp-block-button">
			
				<button class="button wp-element-button"><?= esc_html__("Cancel", "woocommerce"); ?></button>
			</div>
			
		</div>
		
		<div aria-hidden="true" class="wp-block-spacer"></div>
		
	</div>
	
</div>
