<?php

class Magik_Magikfees_Model_Mysql4_Magikfees extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct()
	{
		// Note that the magikfees_id refers to the key field in your database table.
		$this->_init('magikfees/magikfees', 'magikfees_id');
	}
}