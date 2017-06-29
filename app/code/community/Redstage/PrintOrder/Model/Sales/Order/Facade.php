<?php

class Redstage_PrintOrder_Model_Sales_Order_Facade extends Mage_Sales_Model_Abstract
{
    
    /*
     * Identifier for order history item
     */
    const HISTORY_ENTITY_NAME = 'order';
    
    protected $_order;
    
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('sales/order');
    }

    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        $this->setOrderId($order->getId())
            ->setStoreId($order->getStoreId());
        return $this;
    }

    /**
     * Retrieve the order the invoice for created for
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {

        if (!$this->_order instanceof Mage_Sales_Model_Order) {
            $this->_order = Mage::getModel('sales/order')->load($this->getOrderId());
        }
        return $this->_order->setHistoryEntityName(self::HISTORY_ENTITY_NAME);
    }
    
    public function getStore()
    {
        return $this->getOrder()->getStore();
    }
}
