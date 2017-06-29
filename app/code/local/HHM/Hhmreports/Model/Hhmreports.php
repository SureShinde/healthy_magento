<?php
class HHM_Hhmreports_Model_Hhmreports extends Mage_Reports_Model_Mysql4_Order_Collection
{
    function __construct() {
        parent::__construct();
        $this->setResourceModel('sales/sales_flat_order');
        $this->_init('sales/sales_flat_order','entity_id');
   }
 
    public function setDateRange($from, $to) {
        $this->_reset();
        $this->getSelect()
             ->joinInner(array(
                 'i' => $this->getTable('sales/sales_flat_order')),
                 'i.en_id = main_table.entity_id'
                 )
             ->where('i.parent_item_id is null')
             ->where("i.created_at BETWEEN '".$from."' AND '".$to."'")
             ->where('main_table.state = \'complete\'')
             ->columns(array('ordered_qty' => 'count(distinct `main_table`.`entity_id`)'));
        // uncomment next line to get the query log:
         Mage::log('SQL: '.$this->getSelect()->__toString());
        return $this;
    }
 
    public function setStoreIds($storeIds)
    {
        return $this;
    }
 
}
?>