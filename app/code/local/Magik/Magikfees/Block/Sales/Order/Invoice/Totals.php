<?php
class Magik_Magikfees_Block_Sales_Order_Invoice_Totals extends Mage_Sales_Block_Order_Invoice_Totals
{
    protected function _initTotals()
    {       
        parent::_initTotals();
	if(Mage::getStoreConfig('magikfees_section/magikfees_group1/title')!='') {
	  $str= Mage::getStoreConfig('magikfees_section/magikfees_group1/title');
	} else {
	  $str="";
	}
	if ($this->getSource()->getMagikfee() > 0) {
		$magikfees = new Varien_Object(array(
		    'code'      => 'magikfees',
		    'strong'    => false,
		    'value'     => $this->getSource()->getMagikfee(),
		    'base_value'=> $this->getSource()->getBaseMagikfee(),
		    'label'     => $str . Mage::helper('magikfees')->getViewDetails($this->getSource()->getDetailMagikfee()),
		));

		$this->addTotalBefore($magikfees, 'grand_total');
	}
        return $this;
    }
}
