<?php

class Iuvo_Recipe_Model_Resource_Step extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
    {    
    	$this->_init('recipe/step', 'step_id');
    }	
}