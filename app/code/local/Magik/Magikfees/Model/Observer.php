<?php
class Magik_Magikfees_Model_Observer
{

	/*
	* SET THE MAGIK FEES OBSERVER FOR SINGLE CHECKOUT
	*/
	public function setMagikFees($observer)
	{  	

		$quote = $observer->getEvent()->getQuote();
		
		$request = $observer->getEvent()->getRequest();	
		$magikfee = $request->getPost('magikfee', 0);		
		$model1 = Mage::getSingleton('magikfees/magikfees');		
		$modelData = $model1->getCollection();		
		$amount = 0;
		$feestr='';
		$model1->setMagikFeesAddresses(array());
		foreach ($modelData as $data)
		{ 		
			if(isset($magikfee[$data->getMagikfeesId()]) )
			{
				if($data->getFeetype()=='Fixed') 
				{					
					$amount = $amount + $data->getFeeamount();
					$feestr.= " + ".$data->getTitle();			
				}
				else 	
				{
					$amount = $amount + $request->getPost('percent_'.$data->getMagikfeesId());
					$feestr.= " + ".$data->getTitle();
				}
				
			}
			
		}	
		
		$model1->setGiftAmount($amount);
		$model1->setGiftText($feestr);
		
		if (!$quote->isVirtual() && $magikfee){
		    $model1->setMagikFees(1);
		}
		else {
		    $model1->setMagikFees(0);
		}
		$quote->collectTotals()->save();
	}
	
	/*
	* LOAD THE MAGIK FEE FRONTEND FOR MULTISHIPPING CHECKOUT
	*/
	public function setMagikFeesMultishipping($observer)
    {
    	$quote = $observer->getEvent()->getQuote();
        $request = $observer->getEvent()->getRequest();
        $magikfees = $request->getPost('magikfee', array());
	$model1 = Mage::getSingleton('magikfees/magikfees');
        //$model = Mage::getSingleton('magikfees/submagikfees');
        $modelData = $model1->getCollection();
        $amount = 0;
		$feestr='';
		foreach ($modelData as $data)
		{
			if(isset($magikfees[$data->getMagikfeesId()]))
			{
				if($data->getFeetype()=='Fixed') 
				{					
					$amount = $amount + $data->getFeeamount();
					$feestr.= " + ".$data->getTitle();			
				}
				else 	
				{
					$amount = $amount + $request->getPost('percent_'.$data->getMagikfeesId());
					$feestr.= " + ".$data->getTitle();
				}
				
			}
		}
		$model1->setGiftAmount($amount);
		$model1->setGiftText($feestr);
		
        if (!$quote->isVirtual() && !empty($magikfees)){
            $model1->setMagikFees(1);
            $model1->setMagikFeesAddresses(array_keys($magikfees));
        }
        else {
            $model1->setMagikFees(0);
        }
        $quote->collectTotals()->save();
    }    


    public function setProductFees($observer)
    {	

	if($quote_item = $observer->getQuoteItem()->getParentItem()) {	
		//echo ">>".$quote_item->getCalculationPrice();exit;
		$product_id = $quote_item->getProductId();
		$product = $quote_item->getProduct();	
		//Mage::log($product->getTypeInstance()->prepareForCart($buyRequest, $product));
		$myprice=array();	
		$strExtra=array();						
		$productFull = Mage::getModel('catalog/product')->load($product_id);
		
		$attributes = $productFull->getAttributes();
		$overwrite=$attributes['magik_catoverride']->getFrontend()->getValue($productFull);	
		if($overwrite==Mage::helper('magikfees')->__('No'))
		{
			$categoryIds = $product->getCategoryIds();	
			foreach($categoryIds as $km=>$vm) {
				$parentId=Mage::getModel('catalog/category')->load($vm)->getParentId();
				if(!in_array($parentId,$categoryIds))
					array_push($categoryIds,$parentId);
			}
			$fetchcatlist = Mage::getModel('magikfees/magikfees')->getcatFeeList('Category');		
		
			$k=0;		
			foreach($fetchcatlist as $key=>$val)
			{
				if($val['mandatory']=='Yes')	
				{	
					$catArr=array();
					$catArr=explode(",",$val['cat']);
					foreach($catArr as $catId)
					{
					
						if(in_array($catId,$categoryIds)){	
							if($val['feetype']=='Percentage')
							{	
								$myprice['M'.$product_id.'_'.$key][]=($productFull->getFinalPrice()*$val['amt'])/100;
								$myprice['M'.$product_id.'_'.$key][]=$val['flatfee'];								
								$strExtra['M'.$product_id.'_'.$key]=$val['title'];

							} else { 
								
								$myprice['M'.$product_id.'_'.$key][]=$val['amt'];
								$myprice['M'.$product_id.'_'.$key][]=$val['flatfee'];								
								$strExtra['M'.$product_id.'_'.$key]=$val['title'];								
							}
										
						}


					}
				}			
			
			} 	
			
			if($quote_item->getProductType()=='bundle') {
			    if ($quote_item->getHasChildren()) {
			      foreach ($quote_item->getChildren() as $child) {
				  //echo ($child->getProductId())."<br/>";exit;				  
				  $child->setPaymentFee(serialize($myprice));
				  $child->setPaymentStr(serialize($strExtra));				
				  break;
			      }			     
			        
			    }	    
			}
			$quote_item->setPaymentFee(serialize($myprice));
			$quote_item->setPaymentStr(serialize($strExtra));
			
		}
		else
		{					
			$ekarr=$productFull->getMagikExtrafee();			
			$arr=Mage::getModel('magikfees/magikfees')->getFeeList('Product');		
			foreach($arr as $key1=>$val1)
			{
				if($val1['mandatory']=='Yes')	
				{
					foreach($ekarr as $ekey) {
					      if($ekey==$key1)
					      {	
						      if($val1['feetype']=='Percentage')
						      {								
							      $myprice['M'.$product_id.'_'.$key1][]=($productFull->getFinalPrice()*$val1['amt'])/100;
							      $myprice['M'.$product_id.'_'.$key1][]=$val1['flatfee'];							
							      $strExtra['M'.$product_id.'_'.$key1]=$val1['title'];
							      
						      }
						      else { 
							      
							      $myprice['M'.$product_id.'_'.$key1][]=$val1['amt'];
							      $myprice['M'.$product_id.'_'.$key1][]=$val1['flatfee'];
							      $strExtra['M'.$product_id.'_'.$key1]=$val1['title'];								
						      }
					      }
					}
				}			
			}
			if($quote_item->getProductType()=='bundle') {
			    if ($quote_item->getHasChildren()) {
			      foreach ($quote_item->getChildren() as $child) {
				  //echo ($child->getProductId())."<br/>";				  
				  $child->setPaymentFee(serialize($myprice));
				  $child->setPaymentStr(serialize($strExtra));
				  break;
			      }			      
			    }	    
			}
 
			$quote_item->setPaymentFee(serialize($myprice));	
			$quote_item->setPaymentStr(serialize($strExtra));	
			
			
		}

       } else {   
		
		$quote_item = $observer->getQuoteItem();		
		$product_id = $quote_item->getProductId();
		$product = $quote_item->getProduct();		
		$values = unserialize($quote_item->getOptionByCode('info_buyRequest')->getValue());
		$parentId = $values['super_product_config']['product_id'];		
		if(isset($parentId))
		{					
			$this->setMagikQuoteItem($quote_item,$quote_item->getProductId(),$parentId);			
		}
		else 
		{	
			$myprice=array();
			$strExtra=array();		
			$productFull = Mage::getModel('catalog/product')->load($product_id);
			$attributes = $productFull->getAttributes();
			$overwrite=$attributes['magik_catoverride']->getFrontend()->getValue($productFull);	
			if($overwrite==Mage::helper('magikfees')->__('No'))
			{
				$categoryIds = $product->getCategoryIds();	
				foreach($categoryIds as $km=>$vm) {
					$parentId=Mage::getModel('catalog/category')->load($vm)->getParentId();
					if(!in_array($parentId,$categoryIds))
						array_push($categoryIds,$parentId);
				}
				$fetchcatlist = Mage::getModel('magikfees/magikfees')->getcatFeeList('Category');

				if (is_array($fetchcatlist) || is_object($fetchcatlist))
				{
					foreach ($fetchcatlist as $key => $val)
					{
						if ($val['mandatory'] == 'Yes')
						{
							$catArr = explode(",", $val['cat']);
							foreach ($catArr as $catId)
							{
								if (in_array($catId, $categoryIds))
								{
									if ($val['feetype'] == 'Percentage')
									{
										$myprice['M' . $product_id . '_' . $key][] = ($productFull->getFinalPrice() * $val['amt']) / 100;
										$myprice['M' . $product_id . '_' . $key][] = $val['flatfee'];
										$strExtra['M' . $product_id . '_' . $key] = $val['title'];
									} else
									{
										$myprice['M' . $product_id . '_' . $key][] = $val['amt'];
										$myprice['M' . $product_id . '_' . $key][] = $val['flatfee'];
										$strExtra['M' . $product_id . '_' . $key] = $val['title'];
									}
								}
							}
						}
					}
				}
				$quote_item->setPaymentFee(serialize($myprice));
				$quote_item->setPaymentStr(serialize($strExtra));
				
			}
			else
			{	
				$ekarr=$productFull->getMagikExtrafee();										
				$arr=Mage::getModel('magikfees/magikfees')->getFeeList('Product');		
				foreach($arr as $key1=>$val1)
				{
					if($val1['mandatory']=='Yes')	
					{
						foreach($ekarr as $ekey) {
						      if($ekey==$key1)
						      {	
							      if($val1['feetype']=='Percentage')
							      {									      
								      $myprice['M'.$product_id.'_'.$key1][]=($productFull->getFinalPrice()*$val1['amt'])/100;
								      $myprice['M'.$product_id.'_'.$key1][]=$val1['flatfee'];								      
								      $strExtra['M'.$product_id.'_'.$key1]=$val1['title'];								      
							      }
							      else { 									
								      $myprice['M'.$product_id.'_'.$key1][]=$val1['amt'];
								      $myprice['M'.$product_id.'_'.$key1][]=$val1['flatfee'];								     
								      $strExtra['M'.$product_id.'_'.$key1]=$val1['title'];								      
							      }
						      }
						}
					}			
				}						
				$quote_item->setPaymentFee(serialize($myprice));	
				$quote_item->setPaymentStr(serialize($strExtra));
				
			}
		}
	
       }//for simple product	

	
    } 
	
    public function setMagikQuoteItem($quote_item,$product_id,$parent)
    {
			$myprice=array();
			$strExtra=array();	
			$product = $quote_item->getProduct();	
			$productFull = Mage::getModel('catalog/product')->load($parent);
			$attributes = $productFull->getAttributes();
			$overwrite=$attributes['magik_catoverride']->getFrontend()->getValue($productFull);	
			if($overwrite==Mage::helper('magikfees')->__('No'))
			{
				$categoryIds = $product->getCategoryIds();	
				foreach($categoryIds as $km=>$vm) {
					$parentId=Mage::getModel('catalog/category')->load($vm)->getParentId();
					if(!in_array($parentId,$categoryIds))
						array_push($categoryIds,$parentId);
				}
				$fetchcatlist = Mage::getModel('magikfees/magikfees')->getcatFeeList('Category');		
			
				$k=0;		
				foreach($fetchcatlist as $key=>$val)
				{
					if($val['mandatory']=='Yes')	
					{	
						$catArr=array();
						$catArr=explode(",",$val['cat']);
						foreach($catArr as $catId)
						{
						
							if(in_array($catId,$categoryIds)){	
								if($val['feetype']=='Percentage')
								{	
									$myprice['M'.$product_id.'_'.$key][]=($productFull->getFinalPrice()*$val['amt'])/100;
									$myprice['M'.$product_id.'_'.$key][]=$val['flatfee'];							
									$strExtra['M'.$product_id.'_'.$key]=$val['title'];
									
		
								} else {
										
									$myprice['M'.$product_id.'_'.$key][]=$val['amt'];
									$myprice['M'.$product_id.'_'.$key][]=$val['flatfee'];							
									$strExtra['M'.$product_id.'_'.$key]=$val['title'];
									
								}
											
							}
						}
					}			
				
				}		
				$items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
				foreach($items as $item) {
					$values1 = unserialize($item->getOptionByCode('info_buyRequest')->getValue());
					if($parent==$values1['super_product_config']['product_id'])
					{					
						$item->setPaymentFee(serialize($myprice));
						$item->setPaymentStr(serialize($strExtra));						
					}					
				}				
				
				
			}
			else
			{	
				$ekarr=$productFull->getMagikExtrafee();					
				$arr=Mage::getModel('magikfees/magikfees')->getFeeList('Product');		
				foreach($arr as $key1=>$val1)
				{
					if($val1['mandatory']=='Yes')	
					{
						foreach($ekarr as $ekey) {
						      if($ekey==$key1)
						      {	
							      if($val1['feetype']=='Percentage')
							      {	
								      
								      $myprice['M'.$product_id.'_'.$key1][]=($productFull->getFinalPrice()*$val1['amt'])/100;
								      $myprice['M'.$product_id.'_'.$key1][]=$val1['flatfee'];						    
								      $strExtra['M'.$product_id.'_'.$key1]=$val1['title'];
								      
							      }
							      else { 	
									
								      $myprice['M'.$product_id.'_'.$key1][]=$val1['amt'];
								      $myprice['M'.$product_id.'_'.$key1][]=$val1['flatfee'];						     
								      $strExtra['M'.$product_id.'_'.$key1]=$val1['title'];
								      
							      }
						      }
						}
					}			
				}	
				$items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
				foreach($items as $item) {
					$values1 = unserialize($item->getOptionByCode('info_buyRequest')->getValue());
					if($parent==$values1['super_product_config']['product_id'])
					{		
						$item->setPaymentFee(serialize($myprice));
						$item->setPaymentStr(serialize($strExtra));						
					}
				}									
				
				
			}

    }       

    public function updatePaypalCart(Varien_Event_Observer $observer){	
        $paypalCart = $observer->getEvent()->getPaypalCart();
        $additional = $observer->getEvent()->getAdditional();
        $salesEntity = $observer->getEvent()->getSalesEntity(); 
	$feeTitle = Mage::getStoreConfig('magikfees_section/magikfees_group1/title');
        if (!empty($additional) && !empty($salesEntity)) {
            $items = $additional->getItems();
            $items[] = new Varien_Object(array(
                'id'     => $feeTitle!='' ? Mage::helper('magikfees')->__($feeTitle) : Mage::helper('magikfees')->__('Additional Fees'),
                'name'   => $feeTitle!='' ? Mage::helper('magikfees')->__($feeTitle) : Mage::helper('magikfees')->__('Additional Fees'),
                'qty'    => 1,
                'amount' => (float)$salesEntity->getBaseMagikfee(),
            ));
            $salesEntity->setBaseSubtotal($salesEntity->getBaseSubtotal()+$salesEntity->getBaseMagikfee());
            $salesEntity->setBaseTaxAmount($salesEntity->getBaseTaxAmount());
            $additional->setItems($items); 	
        } elseif ($paypalCart && $paypalCart->getSalesEntity()->getBaseMagikfee()!=0) {          
            $paypalCart->addItem($feeTitle!='' ? Mage::helper('magikfees')->__($feeTitle) : Mage::helper('magikfees')->__('Additional Fees'),1,(float)$paypalCart->getSalesEntity()->getBaseMagikfee(),'extrafee');
	    if(Mage::helper('magikfees')->mageVersionCompare(Mage::getVersion(), '1.6.1.0', '>')) {
		if($paypalCart->isShippingAsItem()) {            
		    $paypalCart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_SUBTOTAL, -1* $paypalCart->getSalesEntity()->getBaseShippingAmount());
		}
	    }
        }
  
    }

    public function UpdateMagikQuote(Varien_Event_Observer $observer) 
    {
	$quote = $observer->getQuote();
        foreach ($quote->getAllAddresses() as $address) {
            Mage::getModel('magikfees/sales_quote_address_total_fee')->magikTotal($address);
        }

    }

    public function editOrderAdmin(Varien_Event_Observer $observer)
    {
        /*$observer->getQuoteItem()->setPaymentFee($observer->getOrderItem()->getPaymentFee());
        $observer->getQuoteItem()->setPaymentStr($observer->getOrderItem()->getPaymentStr());
        $observer->getQuoteItem()->save(); */

	if($observer->getQuoteItem()->getParentItem())
	{	
	  $observer->getQuoteItem()->getParentItem()->setPaymentFee($observer->getOrderItem()->getPaymentFee());
	  $observer->getQuoteItem()->getParentItem()->setPaymentStr($observer->getOrderItem()->getPaymentStr());
	  $observer->getQuoteItem()->getParentItem()->save();

	} else {
	  $observer->getQuoteItem()->setPaymentFee($observer->getOrderItem()->getPaymentFee());
	  $observer->getQuoteItem()->setPaymentStr($observer->getOrderItem()->getPaymentStr());
	  $observer->getQuoteItem()->save(); 
	}

    } 
  
    public function UpdateMagikOrderFee(Varien_Event_Observer $observer)
    {    	
    	$cart = $observer->getData('cart');
		$quote = $cart->getData('quote');
		$items = $quote->getAllVisibleItems();

    	$session=Mage::getSingleton('checkout/session');
    	$optfee=unserialize($session->getDetailMagikfee());
    	
    	//$itemtotal=0;
		$subtotal = $session->getQuote()->getSubtotal();
		$optarr=Mage::getModel('magikfees/magikfees')->getOptionaOrderFee($subtotal);
		
    	if(isset($optfee))
    	{
			if (is_array($optfee) || is_object($optfee))
			{
				foreach ($optfee as $key => $value) {
					$feestr=explode("_",$key);
					$feeId=$feestr[1];
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

				if ($resPrice < 0 || ($optarr[0]['magikfees_id']!=$feeId)) {
					return $this->removeAction();
				} else {
					$session->setMagikfee($resPrice);
					$session->setBaseMagikfee($resPrice);
				}
			}

    	} else {
			return $this->removeAction();
	    } 	    
	    //$this->_redirect('checkout/cart');	 

    }

    public function removeAction()
	{
	    $session = Mage::getSingleton('checkout/session');
	    $session->setMagikfee();
	    $session->setBaseMagikfee();
	    $session->setDetailMagikfee();	 
	  
	}
    
}     
