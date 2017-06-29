<?php
	class NeXtIT_QuickReview_Block_Reviewpane extends Mage_Core_Block_Template{
		function __construct()
		{
			parent::__construct();
			$this->setTemplate('nextit/quickreview/notification.phtml');
		}
		public function getReviewData(){
			if(Mage::getSingleton('customer/session')->isLoggedIn()) { //is the customer logged in?
				//Get current customer & Orders
				$customerData = Mage::getSingleton('customer/session')->getCustomer();
				$customerId = $customerData->getId();
				$orderCollection = Mage::getModel('sales/order')->getCollection()
					->addFilter('customer_id', $customerId)
					->setOrder('created_at', Varien_Data_Collection_Db::SORT_ORDER_DESC)
				;
				//Find newest order
				$newestOrderId = $orderCollection->getFirstItem()->getId();
				if($newestOrderId){
					$newestOrder = Mage::getModel('sales/order')->load($newestOrderId);
					//Get items from last order
					$orderItems = $newestOrder->getAllItems();
					$creationDate = $newestOrder->getCreatedAt();
					if(strtotime($creationDate) > strtotime('-3 days')){
						$data['error'] = "Your order is newer than 3 days! We hope you receive it soon!";
					}
					//Loop through items and build simple array
					$itemData = array();
					foreach ($orderItems as &$_item){
						$item = Mage::getModel('catalog/product')->load($_item->getData('product_id'));
						$itemData[$_item->getData('product_id')]['price'] = $item->getPrice();
						$itemData[$_item->getData('product_id')]['name'] = $item->getName();
						$itemData[$_item->getData('product_id')]['image'] = Mage::helper('catalog/image')->init($item, 'image')->resize(75);
					}
					//Sort array by price, pull ID from product with highest price from last order.
					$sortedIds = array_keys($itemData);
					$mostExpensiveItemId = array_shift($sortedIds);
					//Pass data to block
					if($mostExpensiveItemId){
						$data['name'] = $itemData[$mostExpensiveItemId]['name'];
						$data['image'] = $itemData[$mostExpensiveItemId]['image'];
						$data['itemId'] = $mostExpensiveItemId;
						if($this->hasReviewed($mostExpensiveItemId)){
							$data['error'] = "You have already submitted a review for this product! Thank you!";
						}
						return $data;
					}else{
						$data['error'] = "No product found.";
						return $data;
					}
				}else{
					$data['error'] = "No order history found.";
					return $data;
				}

			}else{ // Not logged in, fire error
				$data['error'] = "Please login to view the quick rating pane.";
				return $data;
			}
		}

		public function hasReviewed($itemID)
		{
			//Get current customer
			$customerData = Mage::getSingleton('customer/session')->getCustomer();
			$customerId = $customerData->getId();

			//Get database handlers and set table
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
			$tableName = 'rating_option_vote';

			//Build Query
			$query = "SELECT vote_id FROM ".$tableName." WHERE customer_id = ".$customerId." AND entity_pk_value = ".$itemID;

			//Run the query!
			$results = $readConnection->fetchAll($query);

			if(!empty($results))
			{
				return true;
			}

			else
			{
				return false;
			}
		}
	}