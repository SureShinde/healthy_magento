<?php
class Magik_Magikfees_Block_Adminhtml_Renderer_Magikfeedetail extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    { 
	$sym=Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
	$order = Mage::getModel('sales/order')->loadByIncrementId($row->getData($this->getColumn()->getIndex()));
	$orderFee=unserialize($order->getDetailMagikfee());
	$ordTotal=0;  
	foreach($orderFee as $key=>$val)
	{
	    $ordTotal+=$val['price'];
	}  
	$orderItems = $order->getItemsCollection();
	$Mfee=0;
	foreach ($orderItems as $item){	   
	    $farr=unserialize($item->getPaymentFee());	    
	    foreach($farr as $fkey=>$fval) {
		$Mfee+=$fval[0];
	    } 	
	}
	return $sym.number_format(($Mfee+$ordTotal),2);	
    }

}

?> 