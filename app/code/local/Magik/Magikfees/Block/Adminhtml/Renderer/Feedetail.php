<?php

class Magik_Magikfees_Block_Adminhtml_Renderer_Feedetail extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
	 $sym=Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
	 $data=Mage::getModel('magikfees/magikfees')->load($row->getData($this->getColumn()->getIndex()));       
	 $feestr=''; 
	 if($data['feetype']=='Fixed')
		$feestr=$sym.$data['feeamount'];
	 else
		$feestr=$data['feeamount'].'%';

         return $feestr;
    }
}

?> 
