<?php
class Iuvo_Recipe_Model_Resource_Review_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('recipe/review');
    }
}

?>