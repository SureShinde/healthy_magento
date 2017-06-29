<?php

class Magik_Magikfees_Block_Cart_Item_Renderer extends Mage_Checkout_Block_Cart_Item_Renderer
{
	public function getLoadedProduct()
	{		
	   $productFull=Mage::getModel('catalog/product')->load($this->getProduct()->getId());
	   print_r($this->getItem());exit;
	   return $productFull;
	}

	public function getPaymentFee()
	{	
	    print_r($this->getLoadedProduct()->getPaymentFee())	;	
	    return $this->getLoadedProduct()->getPaymentFee();
	}
	
	public function getPaymentStr()
	{
	    return $this->getLoadedProduct()->getPaymentStr();
	}
}
?>
