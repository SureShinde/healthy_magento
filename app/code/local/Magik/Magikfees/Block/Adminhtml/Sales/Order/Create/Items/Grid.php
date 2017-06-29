<?php
class Magik_Magikfees_Block_Adminhtml_Sales_Order_Create_Items_Grid extends Mage_Adminhtml_Block_Sales_Order_Create_Items_Grid
{ 
    public function __construct()
    {
        parent::__construct();
        $this->setId('magik_magikfees_block_adminhtml_sales_order_create_items_grid');	
    }

     public function fetchView($fileName)
    {
        extract ($this->_viewVars);
        $do = $this->getDirectOutput();
 
        if (!$do) { ob_start(); }
 
        include getcwd().'/app/design/adminhtml/default/default/magikfees/grid.phtml';
	//include getcwd().'/app/code/local/magik/magikfees/block/adminhtml/sales/order/create/items/grid.phtml';
        if (!$do) {$html = ob_get_clean(); }
        else { $html = ''; }
 
        return $html;
    }

}
?>