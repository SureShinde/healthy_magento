<?php
class Magik_Magikfees_Block_Adminhtml_Sales_Order_Totals extends Mage_Adminhtml_Block_Sales_Order_Totals
{
    protected function _initTotals()
    {       
	parent::_initTotals();

	$arr=unserialize($this->getSource()->getDetailMagikfee());
	if(Mage::getStoreConfig('magikfees_section/magikfees_group1/title')!='') {
	    $str=Mage::getStoreConfig('magikfees_section/magikfees_group1/title')."<br/>";
	}
	foreach($arr as $key=>$val)
	{
	    $str.=$val['title'].": ".$val['price']."<br/>";	
	}		 

	if ($this->getSource()->getMagikfee() > 0) {
		$magikfees = new Varien_Object(array(
		    'code'      => 'magikfees',
		    'strong'    => false,
		    'value'     => $this->getSource()->getMagikfee(),
		    'base_value'=> $this->getSource()->getBaseMagikfee(),
		    'label'     => $str,
		));

		$this->addTotalBefore($magikfees, 'grand_total');
	}
        return $this;
    }
}
