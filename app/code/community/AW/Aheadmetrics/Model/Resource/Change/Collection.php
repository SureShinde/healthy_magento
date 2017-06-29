<?php
class AW_Aheadmetrics_Model_Resource_Change_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        $this->_init('awaheadmetrics/change');
    }

    public function joinSalesOrder(){
        if($this->getFlag('order_table_joined')){
           return $this;
        }

        $this->join(array('sales_order_item'=>'sales/order_item'),
            'main_table.entity_id = sales_order_item.item_id');

        $this->setFlag('order_table_joined', true);

    }
}
