<?php

class Magestore_Fbcomment_Model_Mysql4_Fbcomment extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the fbcomment_id refers to the key field in your database table.
        $this->_init('fbcomment/fbcomment', 'fbcomment_id');
    }
}