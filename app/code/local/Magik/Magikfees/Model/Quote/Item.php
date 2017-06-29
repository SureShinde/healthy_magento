<?php
class Magik_Magikfees_Model_Quote_Item extends Mage_Sales_Model_Quote_Item
{
	public function calcRowTotal()
	{	
	    parent::calcRowTotal();
	  
	    $product = $this->getProduct();
	    $product->load($product->getId());	  
	    
	    $farr=unserialize($this->getPaymentFee());	
	   // echo "<pre>";print_r($product);exit;
	    $extra=0;
	    if(!empty($farr)) {
		foreach($farr as $fval)
		{
		    if($fval[1]=='Yes')
			$extra+=$fval[0];
		    else
			$extra+=($fval[0]*$this->getQty());  
		}
		
		$baseTotal = $this->getBaseRowTotal() + $extra;	    
		$total = $this->getStore()->convertPrice($baseTotal);
		$this->setRowTotal($this->getStore()->roundPrice($total));
		$this->setBaseRowTotal($this->getStore()->roundPrice($baseTotal));
		
		return $this;
	    }
	      
	}
}
?>
