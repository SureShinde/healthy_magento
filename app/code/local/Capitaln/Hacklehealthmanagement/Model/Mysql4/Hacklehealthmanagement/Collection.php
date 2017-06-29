<?php

class Capitaln_Hacklehealthmanagement_Model_Mysql4_Hacklehealthmanagement_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('hacklehealthmanagement/hacklehealthmanagement');
    }
}