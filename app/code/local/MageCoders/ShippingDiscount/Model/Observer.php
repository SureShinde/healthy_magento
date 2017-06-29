<?php 
class MageCoders_ShippingDiscount_Model_Observer{
	
	public function processDiscount($observer){ 
		
		if(!Mage::helper('shippingdiscount')->isActive()){
			return;
		}
	
	
		$rule = $observer->getRule();
		$quote = $observer->getQuote();
		$address = $observer->getAddress();
		$item = $observer->getItem();
		
		$result = $observer->getResult();
		
		$shippingAmount = $address->getShippingAmount();		
		$discountAmount = 0;
		
			
		if(!$shippingAmount){
			//$message = Mage::helper('core')->__('Coupon "%s" cannot be applied without shipping amount.', $rule->getCode());
			//$this->_getSession()->addError($message);
			return;
		}
		
		switch($rule->getSimpleAction()){
			case MageCoders_ShippingDiscount_Model_Actions::PERCENT_SHIPPING_DISCOUNT:
				$rulePercent = $rule->getDiscountAmount();
				if($rulePercent>0){
					$_rulePct = $rulePercent/100;	
					$discountAmount = $shippingAmount * $_rulePct;
				}
 				
				break;
			case MageCoders_ShippingDiscount_Model_Actions::FIXED_SHIPPING_DISCOUNT:
				if($rule->getDiscountAmount()<$shippingAmount){
					$discountAmount = $rule->getDiscountAmount();
				}else{
					$discountAmount = $shippingAmount;
				}
				
				break;
		}
		
		if($discountAmount>0){
			$result->setDiscountAmount($discountAmount);
			$result->setBaseDiscountAmount($discountAmount);			
		}
		
		return $result;
	
	}
	
	
	protected function _getSession(){
		return Mage::getSingleton('checkout/session');
	}

	
	
}