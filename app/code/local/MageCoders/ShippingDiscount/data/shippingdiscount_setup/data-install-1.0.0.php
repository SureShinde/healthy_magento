<?php

// add sample "FREESHIPPING" coupon code in shopping cart rules.
	
   $coupon_code = "FREESHIPPING";
   $rule = Mage::getModel('salesrule/rule');
   $freeCoupon = Mage::getModel('salesrule/coupon')->loadByCode($coupon_code);
   
   if(!isset($freeCoupon) && !$freeCoupon->getId()){
   
		$discount = 100;  
		$name = "FREESHIPPING discount";  
		$customerGroups =  Mage::getResourceModel('customer/group_collection')->getAllIds();
	
		$rule->setName($name)
		  ->setDescription($name)
		  ->setFromDate('')
		  ->setCouponType(2)
		  ->setCouponCode($coupon_code)
		  ->setUsesPerCustomer(1)
		  ->setCustomerGroupIds($customerGroups) //an array of customer grou pids
		  ->setIsActive(1)
		  ->setConditionsSerialized('')
		  ->setActionsSerialized('')
		  ->setStopRulesProcessing(0)
		  ->setIsAdvanced(1)
		  ->setProductIds('')
		  ->setSortOrder(0)
		  ->setSimpleAction(MageCoders_ShippingDiscount_Model_Actions::PERCENT_SHIPPING_DISCOUNT)
		  ->setDiscountAmount($discount)
		  ->setDiscountQty(null)
		  ->setDiscountStep(0)
		  ->setSimpleFreeShipping('0')
		  ->setApplyToShipping(1)
		  ->setIsRss(0)
		  ->setWebsiteIds(array(1));
		$rule->save();
   }