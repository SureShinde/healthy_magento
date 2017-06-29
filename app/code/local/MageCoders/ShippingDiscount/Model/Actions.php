<?php
class MageCoders_ShippingDiscount_Model_Actions extends Mage_Core_Model_Abstract{
	
	const PERCENT_SHIPPING_DISCOUNT = 'percent_shipping_discount';
	const FIXED_SHIPPING_DISCOUNT = 'fixed_shipping_discount';	
	
	public function toOptionArray(){
		
			return array(
                Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION => Mage::helper('salesrule')->__('Percent of product price discount'),
                Mage_SalesRule_Model_Rule::BY_FIXED_ACTION => Mage::helper('salesrule')->__('Fixed amount discount'),
                Mage_SalesRule_Model_Rule::CART_FIXED_ACTION => Mage::helper('salesrule')->__('Fixed amount discount for whole cart'),
                Mage_SalesRule_Model_Rule::BUY_X_GET_Y_ACTION => Mage::helper('salesrule')->__('Buy X get Y free (discount amount is Y)'),
                self::PERCENT_SHIPPING_DISCOUNT => Mage::helper('shippingdiscount')->__('Percent of Shipping Amount Discount'),				
                self::FIXED_SHIPPING_DISCOUNT => Mage::helper('shippingdiscount')->__('Fixed price discount to Shipping Amount'),								
				
            );
		
	}
	
}