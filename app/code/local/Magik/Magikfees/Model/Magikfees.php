<?php
class Magik_Magikfees_Model_Magikfees extends Mage_Core_Model_Abstract
{
	protected $_checkout;
	protected $_quote;

	public function _construct()
	{
		parent::_construct();
		$this->_init('magikfees/magikfees');
	}

	/*
	* GET THE CHECKOUT OBJECT
	*/
	public function getCheckout()
	{
		if (empty($this->_checkout)) {
			$this->_checkout = Mage::getSingleton('checkout/session');
		}
		return $this->_checkout;
	}
	
	/*
	* SET MAGIK FEE ADDRESSES
	*/
	public function setMagikFeesAddresses($addresses = array())
	{
		$this->getCheckout()->setMagikFeesAddresses($addresses);
	}
	
	/*
	* LOAD THE MAGIK FEE FRONTEND FOR MULTISHIPPING CHECKOUT
	*/
	public function getMagikFeesAddresses()
	{
		return (array) $this->getCheckout()->getMagikFeesAddresses();
	}	
	
	/*
	* SET MAGIK FEE EXTENSION
	*/
	public function setMagikFees($flag = 1)
	{
		$this->getCheckout()->setMagikFees($flag);
	}

	/*
	* SET GIFT AMOUNT TO BE PAID
	*/
	public function setGiftAmount($amt =0)
	{
		$this->getCheckout()->setGiftAmount($amt);
	}
	
	/*
	* GET GIFT AMOUNT TO BE PAID
	*/
	public function getGiftAmount()
	{
		return $this->getCheckout()->getGiftAmount();
	}
	
	/*
	* GET MAGIK FEES
	*/
	public function getMagikFees()
	{
		return $this->getCheckout()->getMagikFees();
	}
	
	/*
	* GET QUOTE
	*/
	public function getQuote()
	{
		if (empty($this->_quote)) {
			$this->_quote = $this->getCheckout()->getQuote();
		}
		return $this->_quote;
	}
	
	/*
	* SET GIFT TEXT
	*/
	public function setGiftText($feestr)
	{
		$this->getCheckout()->setGiftText($feestr);
	}
	
	/*
	* GET GIFT TEXT
	*/
	public function getGiftText()
	{
		return $this->getCheckout()->getGiftText();
	}

	/*
	* SET Payment FEE EXTENSION
	*/
	public function setPaymentFee($fee)
	{		
        	return $this->getCheckout()->setData('paymentfee', $fee);    	
	}
	
	public function getPaymentFee()
	{
		return $this->getCheckout()->getData('paymentfee');
	}

	public function getFeeList($id,$storeId='')
	{
		if($storeId=='')
		    $storeId=Mage::app()->getStore()->getId();  
		$sym=Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
		$filter_a = array("finset"=>array($storeId));
		$filter_b = array("finset"=>array('0'));
	    $collectionFee=Mage::getModel('magikfees/magikfees')->getCollection();
	    $collectionFee->addFieldToFilter('apply_to',array('eq'=>'Product'))
		    ->addFieldToFilter('status',array('eq'=>'1'))
		    ->addFieldToFilter('store_id',array( $filter_a, $filter_b))
		    ->getSelect(); 

		foreach($collectionFee as $feeDetails)
		{			
			$struct[$feeDetails->getMagikfeesId()]['mandatory']=$feeDetails->getMandatory(); 	
			$struct[$feeDetails->getMagikfeesId()]['feetype']=$feeDetails->getFeetype();
			$struct[$feeDetails->getMagikfeesId()]['amt']=$feeDetails->getFeeamount();
			$struct[$feeDetails->getMagikfeesId()]['flatfee']=$feeDetails->getFlatfee();
			if($feeDetails->getFeetype()=='Fixed')
				$struct[$feeDetails->getMagikfeesId()]['title']=$feeDetails->getTitle().": ".$sym.$feeDetails->getFeeamount();
			else
				$struct[$feeDetails->getMagikfeesId()]['title']=$feeDetails->getTitle().": ".$feeDetails->getFeeamount()."%";		
		}		
		return $struct;
	}

	public function getFeeListAdmin($id)
	{
		$sym=Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
		$collectionFeeAll=Mage::getModel('magikfees/magikfees')->getCollection();
	    $collectionFeeAll->addFieldToFilter('apply_to',array('eq'=>'Product'))
		    ->addFieldToFilter('status',array('eq'=>'1'))
			->getSelect(); 
		foreach($collectionFeeAll as $fee)	
		{	
			$struct[$fee->getMagikfeesId()]['mandatory']=$fee->getMandatory(); 	
			$struct[$fee->getMagikfeesId()]['feetype']=$fee->getFeetype();
			$struct[$fee->getMagikfeesId()]['amt']=$fee->getFeeamount();
			$struct[$fee->getMagikfeesId()]['flatfee']=$fee->getFlatfee();
			if($fee->getFeetype()=='Fixed')
				$struct[$fee->getMagikfeesId()]['title']=$fee->getTitle().": ".$sym.$fee->getFeeamount();
			else
				$struct[$fee->getMagikfeesId()]['title']=$fee->getTitle().": ".$fee->getFeeamount()."%";
		
		}
		return $struct;

	}
	
	public function getcatFeeList($id,$storeId='')
	{
		if($storeId=='')
		    $storeId=Mage::app()->getStore()->getId();
		$sym=Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
		$filter_a = array("finset"=>array($storeId));
		$filter_b = array("finset"=>array('0'));
	    $collectioncatFee=Mage::getModel('magikfees/magikfees')->getCollection();
	    $collectioncatFee->addFieldToFilter('apply_to',array('eq'=>'Category'))
		    ->addFieldToFilter('status',array('eq'=>'1'))
		    ->addFieldToFilter('store_id',array( $filter_a, $filter_b))
		    ->getSelect(); 

		foreach($collectioncatFee as $catfee)	
		{			
			$struct[$catfee->getMagikfeesId()]['mandatory']=$catfee->getMandatory(); 			
			$struct[$catfee->getMagikfeesId()]['feetype']=$catfee->getFeetype();
			$struct[$catfee->getMagikfeesId()]['cat']=$catfee->getCategory();
			$struct[$catfee->getMagikfeesId()]['amt']=$catfee->getFeeamount();
			$struct[$catfee->getMagikfeesId()]['flatfee']=$catfee->getFlatfee();
			if($catfee->getFeetype()=='Fixed')
				$struct[$catfee->getMagikfeesId()]['title']=$catfee->getTitle().": ".$sym.$catfee->getFeeamount();
			else
				$struct[$catfee->getMagikfeesId()]['title']=$catfee->getTitle().": ".$catfee->getFeeamount()."%";
			
		}
		return $struct;

	}

	public function getFeeDetails($cartarr)
	{
		
		$sym=Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
		if(!empty($cartarr)){
			foreach($cartarr as $pid)
			{
				$productFull = Mage::getModel('catalog/product')->load($pid);
				$attributes = $productFull->getAttributes();
				$overwrite=$attributes['magik_catoverride']->getFrontend()->getValue($productFull);
				if($overwrite==Mage::helper('magikfees')->__('No'))
				{
					$categoryIds = $productFull->getCategoryIds();
					foreach($categoryIds as $km=>$vm) {
						$parentId=Mage::getModel('catalog/category')->load($vm)->getParentId();
						if(!in_array($parentId,$categoryIds))
							array_push($categoryIds,$parentId);
					}	
					$fetchcatlist = Mage::getModel('magikfees/magikfees')->getcatFeeList('Category');		
					
					$k=0;		
					foreach($fetchcatlist as $key=>$val)
					{
						if($val['mandatory']=='No')	
						{	
							$catArr=array();
							$catArr=explode(",",$val['cat']);
							foreach($catArr as $catId)
							{
								if(in_array($catId,$categoryIds)){											
									$cartfees[$pid][]=$key;			
								}


							}
						}
					
			
					}
					
				}
				else
				{
					$ekarr=$productFull->getMagikExtrafee();  					
					$arr=Mage::getModel('magikfees/magikfees')->getFeeList('Product');		
					foreach($arr as $key1=>$val1)
					{
						if($val1['mandatory']=='No')	
						{
						    foreach($ekarr as $ekey) {
							if(in_array($ekey,$val1))
							{								
								$cartfees[$pid][]=$key1;	
							}
						    }
						}
						
					}	
					
				}
	


			}


		}		
		return $cartfees;	
	}//end of function
	
	public function getOptFeeDetails($pid,$sid)
	{		
		$sym=Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();		
		$productFull = Mage::getModel('catalog/product')->load($pid);
		$attributes = $productFull->getAttributes();
		$overwrite=$attributes['magik_catoverride']->getFrontend()->getValue($productFull);			
		if($overwrite==Mage::helper('magikfees')->__('No'))
		{
			$categoryIds = $productFull->getCategoryIds();
			foreach($categoryIds as $km=>$vm) {
				$parentId=Mage::getModel('catalog/category')->load($vm)->getParentId();
				if(!in_array($parentId,$categoryIds))
					array_push($categoryIds,$parentId);
			}	
			$fetchcatlist = Mage::getModel('magikfees/magikfees')->getcatFeeList('Category',$sid);		
			
			$k=0;		
			foreach($fetchcatlist as $key=>$val)
			{
				if($val['mandatory']=='No')	
				{	
					$catArr=array();
					$catArr=explode(",",$val['cat']);
					foreach($catArr as $catId)
					{
						if(in_array($catId,$categoryIds)){		
							
							$cartfees[$pid][]=$key;			
						}


					}
				}
			
	
			}
			
		}
		else
		{
			$ekarr=$productFull->getMagikExtrafee();			
			$arr=Mage::getModel('magikfees/magikfees')->getFeeList('Product',$sid);				
			foreach($arr as $key1=>$val1)
			{
				if($val1['mandatory']=='No')	
				{
				    foreach($ekarr as $ekey) {
					   if($ekey==$key1)
					   {							
						   $cartfees[$pid][]=$key1;	
					   }
				    }
				}
				
			}	
			
		}				
		return $cartfees;	
	}//end of function	
	
	public function getMandFeeDetails($pid,$sid)
	{		
		$sym=Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();		
		$productFull = Mage::getModel('catalog/product')->load($pid);
		$attributes = $productFull->getAttributes();
		$overwrite=$attributes['magik_catoverride']->getFrontend()->getValue($productFull);
		if($overwrite==Mage::helper('magikfees')->__('No'))
		{
			$categoryIds = $productFull->getCategoryIds();
			foreach($categoryIds as $km=>$vm) {
				$parentId=Mage::getModel('catalog/category')->load($vm)->getParentId();
				if(!in_array($parentId,$categoryIds))
					array_push($categoryIds,$parentId);
			}	
			$fetchcatlist = Mage::getModel('magikfees/magikfees')->getcatFeeList('Category',$sid);		
			
			$k=0;		
			foreach($fetchcatlist as $key=>$val)
			{					
				$catArr=array();
				$catArr=explode(",",$val['cat']);
				foreach($catArr as $catId)
				{
					if(in_array($catId,$categoryIds)){		
						
						$mcartfees[$pid][]=$key;			
					}
				}	
			}
			
		}
		else
		{
			$ekarr=$productFull->getMagikExtrafee();			
			$arr=Mage::getModel('magikfees/magikfees')->getFeeList('Product',$sid);					
			foreach($arr as $key1=>$val1)
			{	
			      foreach($ekarr as $ekey) {
				  		if($ekey==$key1)
				  		{								
					  		$mcartfees[$pid][]=$key1;	
				  		}
			      }		      
			}	
			
		}	
		
		return $mcartfees;	
	}//end of function

	public function isConfigurableChild($product) 
	{ 
	    $configurable_product = Mage::getModel('catalog/product_type_configurable');
	    $parentIdArray = $configurable_product->getParentIdsByChild($product->getId()); 	
	   
	    if(count($parentIdArray)>0) 
	    { 
		$father = Mage::getModel('catalog/product')->load($parentIdArray[0]); 
		$type_p = $father->getTypeId(); 
		if($type_p == 'configurable') 
		{   
			$tempattr = $father->getTypeInstance()->getConfigurableAttributes();
			$configurableSet['parent']=$parentIdArray[0];
			foreach ($tempattr as $attr)
			{    
			    $configurableSet[] = $attr->getProductAttribute()->getId();                    
			}	
			return $configurableSet;	               	
		} 
		else
		{
		    return false; 	
		}
	    } 


	    
	}
	public function getShipList()
	{		
		$filter_a = array("finset"=>array(Mage::app()->getStore()->getId()));
		$filter_b = array("finset"=>array('0'));
	    $collectionshipFee=Mage::getModel('magikfees/magikfees')->getCollection();
	    $collectionshipFee->addFieldToFilter('apply_to',array('eq'=>'Shipping'))
		    ->addFieldToFilter('status',array('eq'=>'1'))
		    ->addFieldToFilter('store_id',array( $filter_a, $filter_b))
		    ->getSelect();	
		
		return $collectionshipFee->getData();
	}

	public function getFeeTitle($feeid)
	{
		$sym=Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
		$data=Mage::getModel('magikfees/magikfees')->load($feeid);
		if($data['feetype']=='Percentage')
			$str=$data['title'].": ".$data['feeamount']."%";	
		else
			$str=$data['title'].": ".$sym.$data['feeamount'];	
		return $str;

	}

	public function getOrderFee($subtotal) {				
		$filter_a = array("finset"=>array(Mage::app()->getStore()->getId()));
		$filter_b = array("finset"=>array('0'));
	    $collectionman=Mage::getModel('magikfees/magikfees')->getCollection();
	    $collectionman->addFieldToFilter('apply_to',array('eq'=>'Order'))
		    ->addFieldToFilter('status',array('eq'=>'1'))
		    ->addFieldToFilter('mandatory',array('eq'=>'Yes'))
		    ->addFieldToFilter('store_id',array( $filter_a, $filter_b))
		    ->getSelect();
		$addfee=0;
		$feedetail = array();
		foreach($collectionman as $fee)
		{				
			if($fee->getFeetype()=='Fixed'){
				$price = $fee->getFeeamount();								
				$feedetail['M_'.$fee->getMagikfeesId()]['title']=$fee->getTitle();
				//$feedetail['M_'.$fee['magikfees_id']]['price']=Mage::helper('core')->currency($fee['feeamount'],true,false);
			} else { 	
				$price = (($subtotal*$fee->getFeeamount())/100);
				$feedetail['M_'.$fee->getMagikfeesId()]['title']=$fee->getFeeamount()."% ".$fee->getTitle();
				//$feedetail['M_'.$fee['magikfees_id']]['price']=$fee['feeamount']."%";
			}	
			$addfee+=$price;			
			$feedetail['M_'.$fee->getMagikfeesId()]['price']=$price;				
		}	
		$deatials[0]=$addfee;	
		$deatials[1]=$feedetail;		
		return $deatials;
		
	}	

	public function getOptionaOrderFee() 
	{
		$filter_a = array("finset"=>array(Mage::app()->getStore()->getId()));
		$filter_b = array("finset"=>array('0'));
	    $collectionopt=Mage::getModel('magikfees/magikfees')->getCollection();
	    $collectionopt->addFieldToFilter('apply_to',array('eq'=>'Order'))
		    ->addFieldToFilter('status',array('eq'=>'1'))
		    ->addFieldToFilter('mandatory',array('eq'=>'No'))
		    ->addFieldToFilter('store_id',array( $filter_a, $filter_b))
		    ->getSelect();	
		
		return $collectionopt->getData();	
	}	
}	
