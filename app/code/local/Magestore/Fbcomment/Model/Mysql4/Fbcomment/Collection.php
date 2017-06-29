<?php

class Magestore_Fbcomment_Model_Mysql4_Fbcomment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('fbcomment/fbcomment');
    }
}