<?php

class Nick_Trackingimport_Model_Import
{
	
	public function getTrackingGeneralConfig($option){
		return Mage::getStoreConfig('trackingimport/general/'.$option);
	}
	
	public function BeginImport($orderId, $trackingNum, $carrierTitle){
					
				$includeComment = false;
				$comment = NULL;
				$emailCustomer = $this->getTrackingGeneralConfig('email');
				
				
				if ($this->getTrackingGeneralConfig('idselect')) {
					
					//Dispatch Invoice Items
					$invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($orderId);
						
						if($invoice['increment_id'] == "") return ; 
					
					$orderId = $invoice->getOrderId();
					$order = Mage::getModel('sales/order')->load($orderId);
					
				} else {
	
					//Dispatch Entire Order
					$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
						
						if($order['increment_id'] == "") return ; 
				}
				
				$email = $order->getCustomerEmail();
					
				if ($order->canUnhold()) {				
					$order->unhold();
					$order->save();
				}
		
				if (!$this->getTrackingGeneralConfig('idselect') && $order->canInvoice() && $this->getTrackingGeneralConfig('invoice')) {    
					
					$invoice = $order->prepareInvoice();
						if ($this->getTrackingGeneralConfig('capture'))  $invoice->register()->capture();
						else $invoice->register()->pay();
					Mage::getModel('core/resource_transaction')
						->addObject($invoice)
						->addObject($invoice->getOrder())
						->save();
				
					$comment = Mage::helper('trackingimport')->__('Invoice #%s created', $invoice->getIncrementId());
					$orderState = Mage_Sales_Model_Order::STATE_PROCESSING;
					$orderStatus = 'processing';
					$order->setState($orderState, $orderStatus, $comment, $emailCustomer);
					$order->setEmailSent(true);
					$order->save();
				} 	 
		  
				$carrier = 'custom';
				
					if ($carrierTitle == 'ups') {
						$carrier = 'ups';
						$carrierTitle = 'United Parcel Service';
					}
		
					if ($carrierTitle == 'usps') {
						$carrier = 'usps';
						$carrierTitle = 'United States Parcel Service';
					}
					
					if ($carrierTitle == 'dhl') {
						$carrier = 'dhl';
						$carrierTitle = 'DHL';
					}
					
					if ($carrierTitle == 'fedex') {
						$carrier = 'fedex';
						$carrierTitle = 'Federal Express';
					}
					
						
					if ($carrierTitle == 'parcelforce') {
						$carrier = 'parcelforce';
						$carrierTitle = 'Parcel Force';
					}
	
				
				if (!$order->canShip()) {
					
					$shipments = Mage::getResourceModel('sales/order_shipment_collection')
						->addAttributeToSelect('*')
						->setOrderFilter($order->getId())
						->load();
					
					if($shipments < 1) return;
					
						foreach ($order->getShipmentsCollection() as $shipment) {
							
							$shipmentId = $shipment->getIncrementId();
						}
			
					$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
		
						
					$trackingSet = $this->ContainsTracking($shipment, $trackingNum);
				
						if ($trackingNum != NULL && !$trackingSet){ 

							$track = 	Mage::getModel('sales/order_shipment_track')
							->setNumber($trackingNum)
							->setCarrierCode($carrier)
							->setTitle($carrierTitle);
					
							$shipment->addTrack($track)->save();
							
							$status = $this->getTrackingGeneralConfig('importstatus');
							$order->setStatus($status); 
							$order->addStatusToHistory($status, '', $emailCustomer);
						}				
					
						if($shipment){
							if(!$shipment->getEmailSent() && $emailCustomer){
								$shipment->sendEmail(true);
								$shipment->setEmailSent(true);	                          
							}
							
							$shipment->save();
						}   						
				} 
				
				//This converts the order to a shipment
				if ($order->canShip()) {
				
					$convertor = Mage::getModel('sales/convert_order');
					$shipment = $convertor->toShipment($order);
	 				
					if ($this->getTrackingGeneralConfig('idselect')) {
						
						$invoiceItems = array();
						
						foreach ($invoice->getAllItems() as $item) {
							
							$invoiceItems[$item->getOrderItemId()] = $item->getQty();
						
						}
						
						//If selected to import invoice id dispatch only items on the invoice
						
						foreach ($order->getAllItems() as $orderItem) {
							
							foreach ($invoiceItems as $key => $value) {
								
								if($orderItem->getItemId() == $key){
									
									if (!$orderItem->getQtyToShip()) continue 1;
									if ($orderItem->getIsVirtual()) continue 1;
									
									$item = $convertor->itemToShipmentItem($orderItem);
									$qty = $value;
									$item->setQty($qty);
									$shipment->addItem($item);
								}
							}
						}
				
						
					} else {
						
						// Else dispatch all items on the order
						
						foreach ($order->getAllItems() as $orderItem) {
							
							
							if (!$orderItem->getQtyToShip()) continue 1;
							if ($orderItem->getIsVirtual()) continue 1;
		 
							$item = $convertor->itemToShipmentItem($orderItem);
							$qty = $orderItem->getQtyToShip();
							$item->setQty($qty);
							$shipment->addItem($item);
							
						}	
					}
					
					if ($trackingNum != NULL){
						
						$track = 	Mage::getModel('sales/order_shipment_track')
						->setNumber($trackingNum)
						->setCarrierCode($carrier)
						->setTitle($carrierTitle);
					
						$shipment->addTrack($track);
					
					}
					
						$shipment->register();
						$shipment->addComment($comment, $email && $emailCustomer);
						$shipment->setEmailSent(true);
						$shipment->getOrder()->setIsInProcess(true);
		 
						$transactionSave = Mage::getModel('core/resource_transaction')
							->addObject($shipment)
							->addObject($shipment->getOrder())
							->save();
		 	 
						$shipment->save();
						if($emailCustomer) $shipment->sendEmail($email, $comment);
						$order->save();	
						
				} 
				
				
				if (!$order->canShip()) {	
							
					$order->setStatus($this->getTrackingGeneralConfig('importstatus')); 
					$order->addStatusToHistory($this->getTrackingGeneralConfig('importstatus'), '', $emailCustomer);	
					$order->save();	
				}
		}
		
		
		
		
		private function ContainsTracking($shipment, $trackingNum) {
        $exist = false;

			if ($shipment->getOrder()) {
				foreach ($shipment->getOrder()->getTracksCollection() as $track) {
					if (is_object($track->getNumberDetail())) {
						if ($track->getNumberDetail()->gettracking() == $trackingNum)
							$exist = true;
					}
				}
			}
        return $exist;
    }
}
