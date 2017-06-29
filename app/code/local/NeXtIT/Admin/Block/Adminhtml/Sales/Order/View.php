<?php
class NeXtIT_Admin_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{

    public function __construct()
    {
        parent::__construct();

        $order = $this->getOrder();

        if ($order->getStatusLabel() != 'Pending')
        {
            $this->_removeButton('order_edit');
        }
    }

}