<?php
class Magik_Magikfees_Model_Sales_Quote_Address_Total_Fee extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
	protected $_code = 'fee';

	public function collect(Mage_Sales_Model_Quote_Address $address)
	{
		$session = Mage::getSingleton('checkout/session');		
		if(count($session->getQuote()->getAllItems())==0 || $session->getQuoteId()=='')	
		{			
		      $session->setMagikfee();
		      $session->setBaseMagikfee();		    	
		      $session->setDetailMagikfee();		
		}	
		return $this;	      
	}
  
	public function magikTotal(Mage_Sales_Model_Quote_Address $address)
	{

	      $address->setMagikfee(0);
	      $address->setBaseMagikfee(0);

	      //we can return if there are no items
	      $items = $address->getAllItems();
	      if (!count($items)) {
		  return $this;
	      }

	      $quote = $address->getQuote();
	      $quoteId = $quote->getId();
	      if (!is_numeric($quoteId)) {
		  return $this;
	      }
	      Mage::getSingleton('checkout/session')->setFeeList();
	      $mandatoryfee = Mage::getModel('magikfees/magikfees')->getOrderFee($address->getSubtotal());
	      
	      $detailsFees = Mage::getSingleton('checkout/session')->getDetailMagikfee();
	      
	      $taxes = null;      
	      if ($address->getAppliedTaxes()) {
		  $taxes = $address->getAppliedTaxes();
	      }
	      
	      if(!empty($mandatoryfee)) {	      
		      $fee = $mandatoryfee[0];		      
		      $balance=$fee;
		      $address->setBaseMagikfeeAmount($balance);	
		      $address->setMagikfee($balance);
		      $address->setBaseMagikfee($balance);     
		      
		      if ($taxes) {
			  $prices = Mage::helper('magikfees')->getMagikPrice($mandatoryfee[1], $address->getSubtotal(), $address->getShippingInclTax(), $taxes);
			  $taxes = Mage::helper('magikfees')->setMagikTaxes($mandatoryfee[1],$taxes);
			  $address->setAppliedTaxes($taxes);
			  $address->setMagikfeeExclTax($prices->getFeesExclTax());
			  $address->setBaseMagikfeeInclTax($prices->getBaseFeesInclTax());			    
			  $address->setDetailMagikfee(serialize($prices->getDetailsFees()));	
			  $quote->setDetailMagikfee(serialize($prices->getDetailsFees()));		   
			  
		      } else {

			  $address->setDetailMagikfee(serialize($mandatoryfee[1]));
			  $address->setGrandTotal($address->getGrandTotal() + $address->getMagikfee());
			  $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseMagikfee());
		      
			  $quote->setMagikfee($address->getMagikfee());
			  $quote->setBaseMagikfee($address->getBaseMagikfee());
			  $quote->setDetailMagikfee(serialize($mandatoryfee[1]));
			
			  $quote->setGrandTotal($quote->getGrandTotal() + $address->getMagikfee());
			  $quote->setBaseGrandTotal($quote->getBaseGrandTotal()+ $address->getBaseMagikfee());

		      }		 
		      
	      }

	      if($detailsFees!='') {	
		      
		      $orgMArr=unserialize($address->getDetailMagikfee());	
		      $orgOArr=unserialize($detailsFees);
		      if(!empty($orgMArr)) {				  				
			$final=array_merge($orgMArr,$orgOArr);
		      } else {
			$final=$orgOArr; 	  
		      }
		      
		      if ($taxes) {
			  $prices = Mage::helper('magikfees')->getMagikPrice($final, $address->getSubtotal(), $address->getShippingInclTax(), $taxes);
			  
			  $taxes = Mage::helper('magikfees')->setMagikTaxes($final,$taxes);
			  $address->setAppliedTaxes($taxes);
			  $address->setMagikfeeExclTax($prices->getFeesExclTax());
			  $address->setBaseMagikfeeInclTax($prices->getBaseFeesInclTax());
			  
			  $address->setDetailMagikfee(serialize($prices->getDetailsFees()));
			  $address->setBaseMagikfee($address->getBaseMagikfee()+Mage::getSingleton('checkout/session')->getBaseMagikfee());
			  $address->setMagikfee($address->getMagikfee()+ Mage::getSingleton('checkout/session')->getMagikfee());

			  $quote->setDetailMagikfee(serialize($prices->getDetailsFees()));

		      } else {
			  $address->setMagikfee($address->getMagikfee()+Mage::getSingleton('checkout/session')->getMagikfee());
			  $address->setBaseMagikfee($address->getBaseMagikfee()+Mage::getSingleton('checkout/session')->getBaseMagikfee());
			  $address->setDetailMagikfee(serialize($final));				   
			  $address->setGrandTotal($address->getGrandTotal() + Mage::getSingleton('checkout/session')->getMagikfee());
			  $address->setBaseGrandTotal($address->getBaseGrandTotal() + Mage::getSingleton('checkout/session')->getBaseMagikfee());

			  $quote->setMagikfee((float) $quote->getMagikfee()+ Mage::getSingleton('checkout/session')->getMagikfee());
			  $quote->setBaseMagikfee((float) $quote->getBaseMagikfee()+ Mage::getSingleton('checkout/session')->getBaseMagikfee());
			  $quote->setDetailMagikfee(serialize($final));

			  $quote->setGrandTotal($quote->getGrandTotal() + Mage::getSingleton('checkout/session')->getMagikfee());
			  $quote->setBaseGrandTotal($quote->getBaseGrandTotal()+ Mage::getSingleton('checkout/session')->getBaseMagikfee());
		      }		
		      

		      
	      }	
	      if ($taxes) 
	      {					    
		    $address->setGrandTotal($address->getGrandTotal() + $prices->getFees());
		    $address->setBaseGrandTotal($address->getBaseGrandTotal() + $prices->getBaseFeesInclTax());
	      
		    $address->setTaxAmount($address->getTaxAmount() + $prices->getFees() - $address->getMagikfeeExclTax());
		    $address->setBaseTaxAmount($address->getBaseTaxAmount() + $address->getBaseMagikfeeInclTax() -  $prices->getBaseFees());

		    $quote->setGrandTotal($quote->getGrandTotal() + $prices->getFees());
		    $quote->setBaseGrandTotal($quote->getBaseGrandTotal()+ $prices->getBaseFeesInclTax());
	      }  
	      Mage::getSingleton('checkout/session')->setFeeList($address->getDetailMagikfee());
	      return $this;

	}

	public function fetch(Mage_Sales_Model_Quote_Address $address)
	{
// 		$arr=unserialize($address->getDetailMagikfee());		  
// 		
// 		$str=Mage::helper('magikfees')->__('Additional Fees')."<br/>"; 		
// 		foreach($arr as $key=>$val)
// 		{
// 			$str.=$val['title'].": ".Mage::helper('core')->currency($val['price'],true,false)."<br/>";	
// 		}	
		
		$amt = $address->getMagikfee();
		if($amt > 0) {    
		    $address->addTotal(array(
			    'code'=>$this->getCode(),
			    'strong'=> false,
			    'title'=> Mage::getStoreConfig('magikfees_section/magikfees_group1/title')!='' ? Mage::getStoreConfig('magikfees_section/magikfees_group1/title') : Mage::helper('magikfees')->__('Additional Fees'),			
			    'value'=> $amt
		    ));
		}
		return $this;
	}		
	
}
