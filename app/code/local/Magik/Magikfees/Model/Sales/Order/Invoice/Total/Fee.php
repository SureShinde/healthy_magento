<?php
class Magik_Magikfees_Model_Sales_Order_Invoice_Total_Fee extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $orderFee        = $invoice->getOrder()->getMagikfee();
        $baseOrderFee    = $invoice->getOrder()->getBaseMagikfee();
        $detailsOrderFee = $invoice->getOrder()->getDetailMagikfee();

        if ($orderFee >= 0) {
            $invoice->setMagikfee($orderFee);
            $invoice->setBaseMagikfee($baseOrderFee);
	    //$invoice->setDetailMagikfee($detailsOrderFee);
        
	    $this->_prepareDetailsMagikfee($invoice, $detailsOrderFee);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getMagikfee());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getBaseMagikfee());
        }
        return $this;
    }

    private function _prepareDetailsMagikfee($invoice, $detailsFees)
    {
	$helper = Mage::helper('magikfees');
    	$fees = @unserialize($detailsFees);
    	if (is_array($fees) && count($fees)) {
			$tax = null;
			$checkTax = $helper->getFullTaxInfo($invoice->getOrderId());
			if (count($checkTax)) {
				$tax = $checkTax;
			}
    		$prices = $helper->getMagikPrice($fees, $invoice->getSubtotal(), $invoice->getShippingInclTax(), $tax);
    		$detailsFees = serialize($prices->getDetailsFees());
    		$invoice->setMagikfee($prices->getFeesExclTax());
    		$invoice->setBaseMagikfee($prices->getBaseFees());
    	}
    	$invoice->setDetailMagikfee($detailsFees);

    	return $this;
    }
   
}
