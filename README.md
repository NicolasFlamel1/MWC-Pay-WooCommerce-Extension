# MWC Pay WooCommerce Extension

### Description
[MWC Pay](https://github.com/NicolasFlamel1/MWC-Pay) extension for WooCommerce that allows WordPress sites to accept MimbleWimble Coin payments.

### Installing
Download this extension's [newest release](https://github.com/NicolasFlamel1/MWC-Pay-WooCommerce-Extension/releases) and choose to upload it as a plugin on your WordPress site's add plugins page. After it's been installed and activated, a MWC Pay payment option will be available for your customers to use during checkout. [This video tutorial](https://www.youtube.com/watch?v=iplHJ_3qbFM) goes over how to install and use this extension.

This extension relies on you running your own MWC Pay instance. This can be done by downloading and running [MWC Pay's newest release](https://github.com/NicolasFlamel1/MWC-Pay/releases) on the same server that's hosting your WordPress site. Your MWC Pay instance will provide you with its wallet's recovery passphrase that you can use with any MimbleWimble Coin wallet software to access the MimbleWimble Coin that your WordPress site receives as payments.

### Features
This extension provides the following features:
* Adds a MWC Pay payment method to WooCommerce's list of payment methods.
* Allows providing a discount or charging a surcharge to orders that use the MWC Pay payment method.
* Adds a MimbleWimble Coin accepted here badge to the list of available blocks that can be used in WordPress's block editor.
* Adds MimbleWimble Coin to WooCommerce's list of currencies.

### Privacy Considerations
This extension polls the Exchange Rate API, [https://www.exchangerate-api.com](https://www.exchangerate-api.com), once a day to get the currency exchange rates that are used to calculate prices in MimbleWimble Coin. This may expose the IP address of the server hosting your WordPress site to the Exchange Rate API provider, and this may provide some timing information to any networks that the requests pass through. If this is unwanted, then [this line](https://github.com/NicolasFlamel1/MWC-Pay-WooCommerce-Extension/blob/master/mwc-pay-woocommerce-extension.php#L69) can be commented out before you activate this extension. Keep in mind that this extension will only work correctly with that line commented out if your WooCommerce's currency setting is set to USD or MWC.
