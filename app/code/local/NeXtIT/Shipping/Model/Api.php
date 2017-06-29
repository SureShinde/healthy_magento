<?php

class NeXtIT_Shipping_Model_Api extends Mage_Api_Model_Resource_Abstract {

	// Public method to get info
	public function info()
	{
		// Create return
		$return = 'Info request fired!';

		// Return a casted string
		return (string) $return;
	}

	// Public method to set tracking number on an
	// order, provided an order increment id.
	public function addTrackingTo($order_id, $tracking_number, $date = null)
	{
		// All actions are as admin.
		Mage::register('isSecureArea', true);
		$debug = 0;
		if ( $debug === 1 )
		{
			Mage::log('=== API CALLED ===', null, 'fedexapi.log');
			Mage::log('Order To Load : ' . $order_id, null, 'fedexapi.log');
			Mage::log('Tracking Number : ' . $tracking_number, null, 'fedexapi.log');
		}

		// Get the order
		$_order = Mage::getModel('sales/order')->load($order_id, 'increment_id');
		if ( $debug === 1 && $_order->getIncrementId() !== false ) { Mage::log('Order : ' . $_order->getIncrementId(), null, 'fedexapi.log'); }

		// check if has shipments
		if ( $_order->hasShipments() )
		{
			$shipments = $_order->getShipmentsCollection();
			foreach ($shipments as $shipment)
			{
				$shipment->delete();
			}
			$items = $_order->getAllVisibleItems();
			foreach ($items as $i)
			{
				$i->setQtyShipped(0);
				$i->save();
			}
		}

		// Now that the order has no shipments...
		if ( $debug === 1 ) { Mage::log('Order Can Be Shipped!', null, 'fedexapi.log'); }

		// Get items shipped
		$items_array = array();
		foreach ($_order->getAllItems() as $item)
		{
			$items_array[$item->getItemId()] = $item->getQtyOrdered();
		}
		if ( $debug === 1 ) { Mage::log('Items array contains : ' . count($items_array), null, 'fedexapi.log'); }

		if ( ! empty( $items_array ) )
		{
			// Create the shipment
			$shipmentId = Mage::getModel('sales/order_shipment_api')->create(
				$_order->getIncrementId(),
				$items_array,
				'FedEx Shipment Created',
				false,
				false
			);

			// Trigger created date to future.
			if (!is_null($date))
			{
				$datetime = new DateTime($date);
				$datetime->add(new DateInterval('PT4H'));
				$shipment = Mage::getModel('sales/order_shipment');
				$shipment->loadByIncrementId($shipmentId);
				$shipment->setCreatedAt(date('Y-m-d H:i:s', $datetime->getTimestamp()));
				$shipment->save();
			}

			if ( $debug === 1 ) { Mage::log('Shipment Created! : ' . $shipmentId, null, 'fedexapi.log'); }

			if ( $shipmentId )
			{
				// Get the tracking model.
				$trackmodel = Mage::getModel('sales/order_shipment_api');

				// Add tracking data
				$status = $trackmodel->addTrack($shipmentId, 'fedex', 'FedEx', $tracking_number);

				// Add status and mark completed, save.
				$_order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE);
				$_order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
				$_order->save();

				// Return to non-admin
				Mage::unregister('isSecureArea');

				// Status
				return (string) 'success';
			}
		}

		// Return to non-admin
		Mage::unregister('isSecureArea');

		// Notify user of failure
		return (string) 'failure';
	}
}