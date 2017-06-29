<?php
/**
 * Created by PhpStorm.
 * User: mbitson
 * Date: 4/20/2015
 * Time: 8:39 AM
 */

class NeXtIT_Shipping_Model_Observer
{

	// Function to process any unprocessed
	// shipment emails older than 2 hours.
	public static function processShipmentEmails()
	{
		// Start log.
		Mage::log(
			"============= Shipment Email Processor Fired ===========",
			null,
			'shipmentEmails.log'
		);

		// Get times
		$fromTime = date('Y-m-d H:i:s', strtotime('-2 weeks'));
		$toTime = date('Y-m-d H:i:s', strtotime('-2 hours'));

		// Get shipments within our times that have not had an email sent.
		$shipments   = Mage::getResourceModel('sales/order_shipment_collection')
			->addAttributeToSelect('*')
			->addAttributeToFilter('email_sent',
				array(
					'null' => 'thisValueMustBeHereButIsNotUsed'
				)
			)
			->addAttributeToFilter('created_at',
				array(
					'from' => $fromTime,
					'to' => $toTime
				)
			);

		// For each shipment...
		foreach($shipments as $shipment)
		{
			// If this shipment's email has not been sent...
			if ($shipment->getEmailSent() === NULL)
			{
				// Send email
				$shipment->sendEmail(true);

				// Set send flag
				$shipment->setEmailSent(true);

				// Save shipment
				if($shipment->save()){
					Mage::log(
						$shipment->getId()." Shipment emailed and saved!",
						null,
						'shipmentEmails.log'
					);
				}else{
					Mage::log(
						$shipment->getId() . " SHIPMENT FAILURE!",
						null,
						'shipmentEmails.log'
					);
				}
			}
		}

		// End log.
		Mage::log(
			"============= Shipment Email Processor Complete ===========\r\n",
			null,
			'shipmentEmails.log'
		);
	}

}