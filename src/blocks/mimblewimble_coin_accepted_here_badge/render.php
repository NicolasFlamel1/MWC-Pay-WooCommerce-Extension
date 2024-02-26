<?php


// Enforce strict types
declare(strict_types=1);


// Check if file is accessed directly
if(defined("ABSPATH") === FALSE) {

	// Exit
	exit;
}


?>

<div <?= get_block_wrapper_attributes([

	// Style
	"style" => "background: transparent !important; background-color: transparent !important;"
	
]); ?>>

	<a <?= get_block_wrapper_attributes(); ?> href="https://mwc.mw" aria-label="<?= esc_attr__("Go to MimbleWimble Coin's website", "mwc-pay-woocommerce-extension"); ?>" target="_blank" rel="nofollow noopener noreferrer" tabindex="-1">
	
		<img src="<?= esc_url(plugins_url("mimblewimble_coin_logo.svg", __FILE__)); ?>" alt="<?= esc_attr__("MimbleWimble Coin accepted here", "mwc-pay-woocommerce-extension"); ?>">
		
		<p>
		
			<span><?= esc_html__("MimbleWimble Coin", "mwc-pay-woocommerce-extension"); ?></span>
			
			<span><?= esc_html__("Accepted here", "mwc-pay-woocommerce-extension"); ?></span>
			
		</p>
		
	</a>
	
</div>
