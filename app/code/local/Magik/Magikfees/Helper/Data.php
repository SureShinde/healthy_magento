<?php
class Magik_Magikfees_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_modules = null;   
   
    protected $_versionCorrelationEE_CE = array(  
      
      '1.9.1.1' => '1.4.2.0',                  
      '1.10.0.1' => '1.5.0.1',       
      '1.10.1.1' => '1.5.1.0',          
      '1.11.0.2' => '1.6.0.0',          
      '1.11.1.0' => '1.6.1.0',          
    );  
      
  
    protected $_versionCorrelationPE_CE = array(  
      
      '1.10.0.1' => '1.5.0.1',            
    );  

    public function getMagikPrice($fees, $subtotal, $shipping, $taxes = null)
    {	
        $result = array();
        if (is_array($fees) && count($fees)) {
            $resPrice = 0;
            $resPriceInclTax = 0;
            $basePrice = 0;
	    
            foreach ($fees as $feeId => $options) {
		    $Id=explode("_",$feeId);		    
                    $price = 0;
                    $option = Mage::getModel('magikfees/magikfees')->load($Id[1]);			   
                    if($option['feetype']=='Fixed') {
                        $price = $option['feeamount'];
			$fees[$feeId]['title']=$option['title'];
                    } else {
                        if ($subtotal > 0) {                         
                                $price = ($subtotal * $option['feeamount']) / 100;                           
                        }
			$fees[$feeId]['title']=$option['feeamount']."% ".$option['title'];
                    }
		 
                    $priceInclTax = $this->getMagikTax($taxes, $price);
                    $basePrice += $price;
                    $resPriceInclTax += $this->convertPrice($priceInclTax);
                    $resPrice += $this->convertPrice($price);

                    $fees[$feeId]['price'] = $this->convertPrice($price);
                    $fees[$feeId]['price_incl_tax'] = $priceInclTax;
                    $fees[$feeId]['base_price'] = $price;
		    
                //}
            }
            $result['base_fees'] = $basePrice;
            $result['fees_excl_tax'] = $resPrice;
            $result['base_fees_incl_tax'] = $resPriceInclTax;
            $result['fees'] = $resPriceInclTax;
            $result['details_fees'] = $fees;
        }
        return new Varien_Object($result);
    }
    
    public function getMagikTax($taxes, $price, $onlyPercent = false)
    {
        if (is_array($taxes) && count($taxes) && $this->isIncludingTax()) {
            $amount = 0;
            foreach ($taxes as $row) {
                foreach ($row['rates'] as $tax) {
                    if ($tax['percent'] == 0) {
                        continue;
                    }
                    $percent = ($price * $tax['percent']) / 100;
                    $amount += $percent;
                }
            }
            if (true === $onlyPercent) {
                return $amount;
            } else {
                return $price + $amount;
            }
        } else {
            return $price;
        }
    }

    public function setMagikTaxes($detailsFees, $taxes)
    {
        $totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
        $subtotal = $totals["subtotal"]->getValue();

        foreach ($taxes as $i => $tax) {
            foreach ($detailsFees as $feeId => $options) {
		    $Id=substr(trim($feeId),-1);	
                    $price = 0;
                    $option = Mage::getModel('magikfees/magikfees')->load($Id);
                    if ($option['feetype']=='Fixed') {
                        $price = $option['feeamount'];
                    } else {
                        if ($subtotal > 0) {
                            $price = ($subtotal * $option['feeamount']) / 100;
                        }
                    }
                    $tax['amount'] = $tax['amount'] + $this->convertPrice($this->getMagikTax($taxes, $price) - $price);
                    $tax['base_amount'] = $tax['base_amount'] + $this->getMagikTax($taxes, $price) - $price;
              
            }
            $taxes[$i] = $tax;
        }
        return $taxes;
    }

    public function convertPrice($price)
    {
        return Mage::app()->getStore()->convertPrice($price);
    }	

    public function isIncludingTax($store = null)
    {      
	return true;
    }

    public function getViewDetails($detailsFees, $isAdminhtml = false)
    {
        if (!is_array($detailsFees)) {
            $detailsFees = @unserialize($detailsFees);
        }	
        if ($isAdminhtml === true) {
          
        } else {
            $block = Mage::app()->getLayout()
                            ->createBlock('core/template')
                            ->setTemplate('magikfees/detail-fee.phtml')
                            ->addData(array('items' => $detailsFees))
                            ->toHtml();	    
        }
        return $block;
    }

    public function getFullTaxInfo($orderId)
    {
        $order = new Varien_Object(array('id' => $orderId));
        $rates = Mage::getModel('sales/order_tax')->getCollection()->loadByOrder($order)->toArray();
        return Mage::getSingleton('tax/calculation')->reproduceProcess($rates['items']);
    }

    
  
     
   public function mageVersionCompare($version1, $version2, $operator) {  
       
	// Detect edition by included modules  
	if (!$this->_modules) {  
	  $this->_modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());   
	}  
	  
	// Detect enterprise  
	if (in_array('Enterprise_CatalogPermissions', $this->_modules)) {  
	  return version_compare($this->_versionCorrelationEE_CE[$version1] , $version2 , $operator);  
	    
	// Detect professional  
	} elseif (in_array('Enterprise_Enterprise', $this->_modules)) {  
	  return version_compare($this->_versionCorrelationPE_CE[$version1] , $version2 , $operator);  
	  
	// Detect community  
	} else {  
	  return version_compare($version1 , $version2 , $operator);  
	}  
   }  
   


}