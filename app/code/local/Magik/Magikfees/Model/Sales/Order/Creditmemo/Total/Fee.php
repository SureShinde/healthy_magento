<?php
class Magik_Magikfees_Model_Sales_Order_Creditmemo_Total_Fee extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
	public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
	{
	    $orderFee        = $creditmemo->getOrder()->getMagikfee();
	    $baseOrderFee    = $creditmemo->getOrder()->getBaseMagikfee();
	    $detailsOrderFee = $creditmemo->getOrder()->getDetailMagikfee();

	    $creditmemo->setMagikfee($orderFee);
	    $creditmemo->setBaseMagikfee($baseOrderFee);
	    $this->_prepareDetailsMagikfee($creditmemo, $detailsOrderFee);
	    //$creditmemo->setDetailMagikfee($detailsOrderFee);
	    $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getMagikfee());
	    $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getBaseMagikfee());

	    return $this;
	}

	private function _prepareDetailsMagikfee($creditmemo, $detailsFees)
	{
	    $helper = Mage::helper('magikfees');
	    $fees = @unserialize($detailsFees);
	    if (is_array($fees) && count($fees)) {
			    $tax = null;
			    $checkTax = $helper->getFullTaxInfo($creditmemo->getOrderId());
			    if (count($checkTax)) {
				    $tax = $checkTax;
			    }
		    $prices = $helper->getMagikPrice($fees, $creditmemo->getSubtotal(), $creditmemo->getShippingInclTax(), $tax);
		    $detailsFees = serialize($prices->getDetailsFees());
		    $creditmemo->setMagikfee($prices->getFeesExclTax());
		    $creditmemo->setBaseMagikfee($prices->getBaseFees());
	    }
	    $creditmemo->setDetailMagikfee($detailsFees);

	    return $this;
	}
}
