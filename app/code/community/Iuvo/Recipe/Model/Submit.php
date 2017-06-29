<?php

class Iuvo_Recipe_Model_Submit extends Mage_Core_Model_Abstract
{
	public function _construct()
    {    
        parent::_construct();
    	$this->_init('recipe/submit');
    }    
}