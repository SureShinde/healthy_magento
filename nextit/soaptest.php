<?php
// Clear WSDL cache
ini_set('soap.wsdl_cache_enabled', '0');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Make sure SOAP extension loaded
if(extension_loaded('soap'))
{
	// Set connection URL, username, and password
	$api_url_v2 = "https://www.hackleyhealthmanagement.com/api/v2_soap/?wsdl=1";
	$username = 'fedex';
	$password = '1fea!@$@!fedexP4$$w0rd!@';

	// Create a PHP SoapClient
	$cli = new SoapClient($api_url_v2);

	// Login and get session ID for future calls.
	$session_id = $cli->login($username, $password);

	// Call add shipment tracking number method via SOAP
	// @params : Session Id, Order Number, FedEx Tracking Number
	$result = $cli->nitShipmentAddTrackingTo($session_id, '100016710', '12345671234567', '2015-04-21 14:01:01');

	// Output a message based on the result of the SOAP request.
	if($result === 'success'){
		echo 'Tracking Number Added Correctly!';
	}elseif($result === 'failure'){
		echo 'Tracking Number Not Added';
	}
}

// Display error if SOAP is not loaded.
else
{
	// TODO: Display error message
	echo 'We Need SOAP!';
}