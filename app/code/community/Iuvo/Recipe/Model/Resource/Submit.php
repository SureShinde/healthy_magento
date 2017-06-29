<?php

class Iuvo_Recipe_Model_Resource_Submit extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
    {    
    	$this->_init('recipe/submit', 'submit_id');
    }	
}