<?php

class Iuvo_Recipe_Model_Type extends Mage_Core_Model_Abstract
{
	public function _construct()
    {    
        parent::_construct();
    	$this->_init('recipe/type');
    }    
}