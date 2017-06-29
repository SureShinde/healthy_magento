<?php

class Magik_Magikfees_Model_Mysql4_Magikfees_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{

		parent::_construct();
		$this->_init('magikfees/magikfees');
	}
	public function addFilterByApplyTo($id) {
        	$this->addFieldToFilter('apply_to', $id);
        	return $this;
    	}

   	public function addAttributeToSort($attribute, $dir='asc') 
	{ 
		if (!is_string($attribute)) { 
			return $this; 
		} 
		$this->setOrder($attribute, $dir); 
		return $this; 
	}
}
