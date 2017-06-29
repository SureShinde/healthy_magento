<?php

require_once 'app/Mage.php';
Mage::app();

$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');
$writeConnection = $resource->getConnection('core_write');

$today = date("Y-m-d"); 
$query = "SELECT * FROM `custom_sales_flat_quote_item` WHERE `nextshipdate` = '".$today."'";
$results  = $readConnection->fetchAll($query);	
//echo "<pre>";print_r($results); echo "</pre>";exit;
foreach($results as $row) {

		$query1 = 'SELECT `product_id`, `row_total`, `order_id` FROM `sales_flat_order_item` WHERE `quote_item_id`='.$row['item_id'];
		$getProduct  = $readConnection->fetchAll($query1);

		$query2 = 'SELECT `customer_id` FROM `sales_flat_order` WHERE `entity_id`='.$getProduct[0]['order_id'];
		$getCustomerId  = $readConnection->fetchOne($query2);

		$id=12; // get Customer Id
		$customer = Mage::getModel('customer/customer')->load($getCustomerId);

		$transaction = Mage::getModel('core/resource_transaction');
		$storeId = $customer->getStoreId();

		$_product = Mage::getModel('catalog/product')->load($row['subscribed_prod_id']);

		//echo $storeId;
		$reservedOrderId = Mage::getSingleton('eav/config')->getEntityType('order')->fetchNewIncrementId($storeId);
		//echo $reservedOrderId;exit;
		$order = Mage::getModel('sales/order')
		->setIncrementId($reservedOrderId)
		->setStoreId($storeId)
		->setQuoteId(0)
		->setGlobal_currency_code('USD')
		->setBase_currency_code('USD')
		->setStore_currency_code('USD')
		->setOrder_currency_code('USD');

		// set Customer data
		$order->setCustomer_email($customer->getEmail())
		->setCustomerFirstname($customer->getFirstname())
		->setCustomerLastname($customer->getLastname())
		->setCustomerGroupId($customer->getGroupId())
		->setCustomer_is_guest(0)
		->setCustomer($customer);

		// set Billing Address
		$billing = $customer->getDefaultBillingAddress();
		$billingAddress = Mage::getModel('sales/order_address')
		->setStoreId($storeId)
		->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
		->setCustomerId($customer->getId())
		->setCustomerAddressId($customer->getDefaultBilling())
		->setCustomer_address_id($billing->getEntityId())
		->setPrefix($billing->getPrefix())
		->setFirstname($billing->getFirstname())
		->setMiddlename($billing->getMiddlename())
		->setLastname($billing->getLastname())
		->setSuffix($billing->getSuffix())
		->setCompany($billing->getCompany())
		->setStreet($billing->getStreet())
		->setCity($billing->getCity())
		->setCountry_id($billing->getCountryId())
		->setRegion($billing->getRegion())
		->setRegion_id($billing->getRegionId())
		->setPostcode($billing->getPostcode())
		->setTelephone($billing->getTelephone())
		->setFax($billing->getFax());
		$order->setBillingAddress($billingAddress);

		$shipping = $customer->getDefaultShippingAddress();
		$shippingAddress = Mage::getModel('sales/order_address')
		->setStoreId($storeId)
		->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
		->setCustomerId($customer->getId())
		->setCustomerAddressId($customer->getDefaultShipping())
		->setCustomer_address_id($shipping->getEntityId())
		->setPrefix($shipping->getPrefix())
		->setFirstname($shipping->getFirstname())
		->setMiddlename($shipping->getMiddlename())
		->setLastname($shipping->getLastname())
		->setSuffix($shipping->getSuffix())
		->setCompany($shipping->getCompany())
		->setStreet($shipping->getStreet())
		->setCity($shipping->getCity())
		->setCountry_id($shipping->getCountryId())
		->setRegion($shipping->getRegion())
		->setRegion_id($shipping->getRegionId())
		->setPostcode($shipping->getPostcode())
		->setTelephone($shipping->getTelephone())
		->setFax($shipping->getFax());
		if($_product->getPrice() > 200) {
		$order->setShippingAddress($shippingAddress)
		->setShippingMethod('Standard FedEx Ground')
		->setShippingDescription('Free Shipping Over $200! - Standard FedEx Ground');
		} else {

		$order->setShippingAddress($shippingAddress)
		->setShippingMethod('Standard FedEx Ground')
		->setShippingDescription('Flat Rate - Standard FedEx Ground');

		}

		$orderPayment = Mage::getModel('sales/order_payment')
		->setStoreId($storeId)
		->setCustomerPaymentId(0)
		->setMethod('purchaseorder')
		->setPo_number(' - ');
		$order->setPayment($orderPayment);

		// let say, we have 2 products
		$subTotal = 0;
		//$products = array('1' => array('qty' => 1),'2' =>array('qty' => 1));
		//foreach ($products as $productId=>$product) {
		
		$rowTotal = $_product->getPrice() * 1;
		$orderItem = Mage::getModel('sales/order_item')
		->setStoreId($storeId)
		->setQuoteItemId(0)
		->setQuoteParentItemId(NULL)
		->setProductId($productId)
		->setProductType($_product->getTypeId())
		->setQtyBackordered(NULL)
		->setTotalQtyOrdered($product['rqty'])
		->setQtyOrdered($product['qty'])
		->setName($_product->getName())
		->setSku($_product->getSku())
		->setPrice($_product->getPrice())
		->setBasePrice($_product->getPrice())
		->setOriginalPrice($_product->getPrice())
		->setRowTotal($rowTotal)
		->setBaseRowTotal($rowTotal);

		$subTotal += $rowTotal;
		$order->addItem($orderItem);
		//}

		$order->setSubtotal($subTotal)
		->setBaseSubtotal($subTotal)
		->setGrandTotal($subTotal)
		->setBaseGrandTotal($subTotal);

		$transaction->addObject($order);
		$transaction->addCommitCallback(array($order, 'place'));
		$transaction->addCommitCallback(array($order, 'save'));
		$transaction->save();
		//echo "<pre>";print_r($row); echo "</pre>";
		$two_weeks_order_delivery_date = date("Y-m-d", mktime(0,0,0,date('m'),date('d')+14,date('Y')));
		$query = "UPDATE `custom_sales_flat_quote_item` SET `nextshipdate` = '".$two_weeks_order_delivery_date."' where occurence_id = ".$row['occurence_id'];
		//echo $query;
		$writeConnection->query($query);

}


?>