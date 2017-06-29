<?php
class Capitaln_Hacklehealthmanagement_Block_Hacklehealthmanagement extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getHacklehealthmanagement()     
     { 
        if (!$this->hasData('hacklehealthmanagement')) {
            $this->setData('hacklehealthmanagement', Mage::registry('hacklehealthmanagement'));
        }
        return $this->getData('hacklehealthmanagement');
        
    }
}