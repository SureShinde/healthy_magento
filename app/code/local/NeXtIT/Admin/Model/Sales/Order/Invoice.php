<?php
/**
* @category   NeXtIT
* @package    Admin
* @since      File available since Release 0.1.0
*/
class NeXtIT_Admin_Model_Sales_Order_Invoice extends Mage_Sales_Model_Order_Invoice
{

    /**
     * Get invoice items collection
     *
     * CUSTOMIZATION: Order invoice items by name
     *
     * @return Mage_Sales_Model_Mysql4_Order_Invoice_Item_Collection
     */
    public function getItemsCollection()
    {
        if (empty($this->_items))
        {
            $this->_items = Mage::getModel('sales/order_invoice_item')
                                ->getCollection()
                                ->setInvoiceFilter($this->getId())
                                ->addAttributeToSelect('*')
                                ->setOrder('sku', 'asc');

            if ($this->getId())
            {
                foreach ($this->_items as $item)
                {
                    $item->setInvoice($this);
                }
            }

        }
        return $this->_items;
    }

}