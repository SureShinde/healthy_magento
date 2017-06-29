<?php
class Magik_Magikfees_Model_Sales_Quote_Address_Total_Magikfees extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
	public function collect(Mage_Sales_Model_Quote_Address $address)
	{
		$model = Mage::getSingleton('magikfees/magikfees');

		/*
		* CHECK IF MAGIK FEES IS ACTIVE
		*/
		if (Mage_Sales_Model_Quote_Address::TYPE_SHIPPING == $address->getAddressType() &&  $model->getMagikFees())
		{
			$amount = $model->getGiftAmount();
			$txt=$model->getGiftText();	
			$method = $address->getShippingMethod();
			if($method)
			{
				$address->setShippingAmount($address->getShippingAmount() + $amount);
				$address->setBaseShippingAmount($address->getBaseShippingAmount() + $amount);
				//$address->setGrandTotal($address->getGrandTotal() + $amount);
				$address->setShippingDescription($address->getShippingDescription().$txt);
				//$address->setBaseGrandTotal($address->getBaseGrandTotal() + $amount);
			}
		}
		return $this;
	}


	public function fetch(Mage_Sales_Model_Quote_Address $address)
	{
		return $this;
	}
}
