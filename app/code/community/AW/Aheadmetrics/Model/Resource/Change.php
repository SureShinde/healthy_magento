<?php

class AW_Aheadmetrics_Model_Resource_Change extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('awaheadmetrics/change', 'id');
    }
}
