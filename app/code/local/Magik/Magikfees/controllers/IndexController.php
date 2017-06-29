<?php
class Magik_Magikfees_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$flag=0;
			
		$post=$this->getRequest()->getParams();	
		//print_r($post);exit;
		$sym=Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
		$items = Mage::getSingleton('checkout/session')->getQuote()->getAllVisibleItems();
		
		foreach($items as $item)
		{		
			$myprice=unserialize($item->getPaymentFee());			
			$strExtra=unserialize($item->getPaymentStr());						
			if($post['cval']==0)
			{
				unset($myprice[$post['cname']]);		
				unset($strExtra[$post['cname']]);
			}
			else {
				$myval=explode("_",$post['cval']);
				
				if($myval[0]==$item->getProductId()){ 
					if($item->getProduct_type()=="bundle") {
														
					    $item_id =  $item->getId()+1;
					    $bundle_item = Mage::getModel('sales/quote_item')->load($item_id);					    
					    $product_id  = $bundle_item->getProduct_id();				
					
					} else {
					    $product_id =  $item->getProductId();	
					}

					$feedata=Mage::getModel('magikfees/magikfees')->load($myval[1]);
					if($feedata['feetype']=='Percentage'){
						
					    $myprice['O'.$item->getProductId()."_".$myval[1]][]=($item->getPrice()*$feedata['feeamount']/100);
					    $myprice['O'.$item->getProductId()."_".$myval[1]][]=$feedata['flatfee'];
					    $strExtra['O'.$item->getProductId()."_".$myval[1]]=$feedata['title'].": ".$feedata['feeamount']."%";
					    $magikarr['O'.$item->getProductId()."_".$myval[1]]=$post['cval'];							

					} else {
				
						$myprice['O'.$item->getProductId()."_".$myval[1]][]=$feedata['feeamount'];
						$myprice['O'.$item->getProductId()."_".$myval[1]][]=$feedata['flatfee'];
						$strExtra['O'.$item->getProductId()."_".$myval[1]]=$feedata['title'].": ".$sym.$feedata['feeamount'];
						$magikarr['O'.$item->getProductId()."_".$myval[1]]=$post['cval'];					

					} 
					
				}//if				
				
		     	}//else 

			if($item->getProduct_type()=="bundle")
			{			
			      $item->setPaymentFee(serialize($myprice));		
			      $item->setPaymentStr(serialize($strExtra));
			      $item->setMagikExtrafee(serialize($magikarr));						
			      $item->save();

			      $item_id =  $item->getId()+1;
			      $sales_flat_quote_item_table = Mage::getSingleton('core/resource')->getTableName('sales_flat_quote_item');
			      $db = Mage::getSingleton('core/resource')->getConnection('core_write');
			      $qry = "UPDATE ".$sales_flat_quote_item_table." SET payment_fee='".serialize($myprice)."',payment_str='".serialize($strExtra)."',magik_extrafee='".serialize($magikarr)."' WHERE item_id='$item_id'";
			      $db->query($qry);
			     

			} else {
			      $item->setPaymentFee(serialize($myprice));		
			      $item->setPaymentStr(serialize($strExtra));
			      $item->setMagikExtrafee(serialize($magikarr));						
			      $item->save();
			}
		}
		
	}

	public function optOrderAction()
	{
	    $post=$this->getRequest()->getParams();	
	    
	    $session = Mage::getSingleton('checkout/session');
	   
            $resPrice = 0;
            $optFee = $this->getRequest()->getPost('opt');	   
         
            $detailsFees = array();
	    $subtotal = $session->getQuote()->getSubtotal();
           
            if ($optFee) {                     
                foreach ($post['opt'] as $feeId) {
                   $feedetail=Mage::getModel('magikfees/magikfees')->load($feeId);                 
                   if($feedetail['feetype']=='Fixed') {
		       $price = $feedetail['feeamount'];
		       $detailsFees['O_'.$feedetail['magikfees_id']]['title']=$feedetail['title'];
                   }										  
		   else { 	 
		       $price = (($subtotal*$feedetail['feeamount'])/100);
		       $detailsFees['O_'.$feedetail['magikfees_id']]['title']=$feedetail['feeamount']."% ".$feedetail['title'];		      	    
		   } 
		   $resPrice+=$price;		   
		   $detailsFees['O_'.$feedetail['magikfees_id']]['price']=$price;                                 
                }		
                $session->setDetailMagikfee(serialize($detailsFees));              

		if ($resPrice < 0) {
		    return $this->removeAction();
		} else {
		    $session->setMagikfee($resPrice);
		    $session->setBaseMagikfee($resPrice);
		}					      
            } else {
		return $this->removeAction();
	    } 
	    $this->_redirect('checkout/cart');	  
		
	}	
	public function removeAction()
	{
	    $session = Mage::getSingleton('checkout/session');
	    $session->setMagikfee();
	    $session->setBaseMagikfee();
	    $session->setDetailMagikfee();	 
	    $this->_redirect('checkout/cart');   
	}
}
