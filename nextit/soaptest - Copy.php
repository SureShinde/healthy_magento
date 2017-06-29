<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');

// Clear WSDL cache
ini_set('soap.wsdl_cache_enabled', '0');

// Make sure soap extension loaded
if(extension_loaded('soap'))
{
	$mage_url = 'https://www.hackleyhealthmanagement.com/api/v2_soap/?wsdl';
	$mage_user = 'fedex';
	$mage_api_key = '1fea!@$@!fedexP4$$w0rd!@';

	// Create soap client, get functions
	$soap = new SoapClient( $mage_url );

	// Start session & login
	$soap->startSession();
	$session = $soap->login( $mage_user, $mage_api_key );

	// Get result!
	$result = $soap->nextitShippingInfo( $session );

	// Testing...
	echo '<pre>'; var_dump($result); echo '</pre>';
	exit;
}
else
{
	// TODO: Display error message
	echo 'We Need SOAP!';
}