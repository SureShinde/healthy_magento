<?php
class MageCoders_ShippingDiscount_Helper_Data extends Mage_Core_Helper_Abstract{
		
	public function isShipAction($action){
		$shipActions = array(MageCoders_ShippingDiscount_Model_Actions::PERCENT_SHIPPING_DISCOUNT,MageCoders_ShippingDiscount_Model_Actions::FIXED_SHIPPING_DISCOUNT);
		if(in_array($action,$shipActions)){
			return true;
		}
		return false; 
	}		
	
	public function isActive(){
		return $this->getConfig('active');
	}
		
	public function getConfig($key){
		if(!empty($key)){
			return Mage::getStoreConfig('shippingdiscount/settings/'.$key);
		}
	}
}