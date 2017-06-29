<?php
class Magik_Magikfees_Model_Adminhtml_Order_Create extends Mage_Adminhtml_Model_Sales_Order_Create
{
     public function addProduct($product, $config = 1)
    {
    	
        if (!is_array($config) && !($config instanceof Varien_Object)) {
            $config = array('qty' => $config);
            
        }
        $config = new Varien_Object($config);

        if (!($product instanceof Mage_Catalog_Model_Product)) {
            $productId = $product;
            $product = Mage::getModel('catalog/product')
                ->setStore($this->getSession()->getStore())
                ->setStoreId($this->getSession()->getStoreId())
                ->load($product);
            if (!$product->getId()) {
                Mage::throwException(
                    Mage::helper('adminhtml')->__('Failed to add a product to cart by id "%s".', $productId)
                );
            }
        }
        
        $stockItem = $product->getStockItem();
        if ($stockItem && $stockItem->getIsQtyDecimal()) {
            $product->setIsQtyDecimal(1);
        }

        $product->setCartQty($config->getQty());
        $item = $this->getQuote()->addProductAdvanced(
            $product,
            $config,
            Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_FULL
        );
        
        if (is_string($item)) {
            if ($product->getTypeId() != Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE) {
                $item = $this->getQuote()->addProductAdvanced(
                    $product,
                    $config,
                    Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_LITE
                );              
            }
            if (is_string($item)) {
                Mage::throwException($item);
            }
        }      
        $item->checkData();
		  $parent_item =  $item->getParentItem();
		  
        $item = ($parent_item) ? $parent_item : $item;
        //$productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
        //$config->toArray()
 	     //Mage::log($config->getData());
		  $values = unserialize($item->getOptionByCode('info_buyRequest')->getValue());
		  $parentId = $values['super_product_config']['product_id'];
		
		  if(isset($parentId))
		  		$this->getGroupFeeM($item,$productId,$parentId,$this->getQuote());
		  else 
		  		$this->getFeeM($item,$productId);
		  			
        $this->setRecollect(true);
        return $this;
    }
    
    
    
   public function getFeeM($quote_item,$product_id)
   {	  	  		
			$myprice=array();
			$strExtra=array();		
			$product = Mage::getModel('catalog/product')->load($product_id);			
			$overwrite=$product->getMagikCatoverride();			
			if($overwrite==0)
			{
				$categoryIds = $product->getCategoryIds();	
				foreach($categoryIds as $km=>$vm) {
					$parentId=Mage::getModel('catalog/category')->load($vm)->getParentId();
					if(!in_array($parentId,$categoryIds))
						array_push($categoryIds,$parentId);
				}
				$fetchcatlist = Mage::getModel('magikfees/magikfees')->getcatFeeList('Category',$this->getSession()->getStoreId());		
			
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
									$myprice['M'.$product_id.'_'.$key][]=($product->getFinalPrice()*$val['amt'])/100;
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
				      foreach ($quote_item->getChildren() as $child) {					  				  
					  $child->setPaymentFee(serialize($myprice));
					  $child->setPaymentStr(serialize($strExtra));					 
					  break;
				      }		       
				}
				$quote_item->setPaymentFee(serialize($myprice));
				$quote_item->setPaymentStr(serialize($strExtra));
				$quote_item->save();
				
			}
			else
			{	
				$ekarr=$product->getMagikExtrafee();										
				$arr=Mage::getModel('magikfees/magikfees')->getFeeList('Product',$this->getSession()->getStoreId());					
				foreach($arr as $key1=>$val1)
				{
					if($val1['mandatory']=='Yes')	
					{
						foreach($ekarr as $ekey) {
						      if($ekey==$key1)
						      {	
							      if($val1['feetype']=='Percentage')
							      {								      
								      $myprice['M'.$product_id.'_'.$key1][]=($product->getFinalPrice()*$val1['amt'])/100;
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
				     foreach ($quote_item->getChildren() as $child) {					  
					  		$child->setPaymentFee(serialize($myprice));
					  		$child->setPaymentStr(serialize($strExtra));					 
					  		break;
				      }				     
				}		
				$quote_item->setPaymentFee(serialize($myprice));	
				$quote_item->setPaymentStr(serialize($strExtra));
				$quote_item->save();
				
			}
			
	}
	
	public function getGroupFeeM($quote_item,$product_id,$parent,$quote)
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
				$fetchcatlist = Mage::getModel('magikfees/magikfees')->getcatFeeList('Category',$this->getSession()->getStoreId());		
			
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
				$quote_item->setPaymentFee(serialize($myprice));
				$quote_item->setPaymentStr(serialize($strExtra));
				$quote_item->save();					
			
			}
			else
			{	
				$ekarr=$productFull->getMagikExtrafee();					
				$arr=Mage::getModel('magikfees/magikfees')->getFeeList('Product',$this->getSession()->getStoreId());		
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
				$quote_item->save();				
				
			}		
		
	}	
    
}  
?>