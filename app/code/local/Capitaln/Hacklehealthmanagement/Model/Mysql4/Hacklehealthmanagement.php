<?php

class Capitaln_Hacklehealthmanagement_Model_Mysql4_Hacklehealthmanagement extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the hacklehealthmanagement_id refers to the key field in your database table.
        $this->_init('hacklehealthmanagement/hacklehealthmanagement', 'hacklehealthmanagement_id');
    }
}