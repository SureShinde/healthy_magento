<?php
class AW_Aheadmetrics_Model_Reports_Salesreport extends AW_Aheadmetrics_Model_Reports_Abstract
{

    const REPORT_CODE = 'SalesByProductAttributesReport';

    private function getAttributes()
    {
        //get All Attributes
        $result = array();
        $attributesSet = Mage::getResourceModel('catalog/product_attribute_collection');
        foreach ($attributesSet as $item) {
            $options = $item->getSource()->getAllOptions(true, true);
            $values = array();

            foreach ($options as $option) {

                if ($option['value'] === '') {
                    continue;
                }

                $values[] = array('value'=>$option['label'],'label'=>$option['label']);
            }
            $result[] = array_merge($item->getData(), array('values' => $values));
        }
        return $result;
    }


    private function addOrderAttributes()
    {
        $orderAttr = array(

            array('attribute_code' => 'date',
                'is_visible' => 0,
                'on_print' => 1,
                'frontend_label' => 'Date'),

            array('attribute_code' => 'store_id',
                'is_visible' => 0,
                'on_print' => 1,
                'frontend_label' => 'Store ID'),


            array('attribute_code' => 'order_state',
                'is_visible' => 0,
                'on_print' => 1,
                'frontend_label' => 'State'),


            array('attribute_code' => 'order_status',
                'is_visible' => 0,
                'on_print' => 1,
                'frontend_input' => 'select',
                'frontend_label' => 'Order Status',
                'values' => Mage::getModel('sales/order_status')->getResourceCollection()->getData()),

            array('attribute_code' => 'qty_ordered',
                'is_visible' => 0,
                'on_print' => 1,
                'frontend_label' => 'Qty Ordered'),

            array('attribute_code' => 'subtotal',
                'is_visible' => 0,
                'on_print' => 1,
                'frontend_label' => 'Subtotal'),

            array('attribute_code' => 'shipping',
                'is_visible' => 0,
                'on_print' => 1,
                'frontend_label' => 'Shipping'),

            array('attribute_code' => 'tax',
                'is_visible' => 0,
                'on_print' => 1,
                'frontend_label' => 'Tax'),

            array('attribute_code' => 'total',
                'is_visible' => 0,
                'on_print' => 1,
                'frontend_label' => 'Total'),
            array('attribute_code' => 'discount_amount',
                'is_visible' => 0,
                'on_print' => 1,
                'frontend_label' => 'Discount amount'),
            array('attribute_code' => 'invoiced',
                'is_visible' => 0,
                'on_print' => 1,
                'frontend_label' => 'Invoiced'),

            array('attribute_code' => 'invoced',
                'is_visible' => 0,
                'on_print' => 1,
                'frontend_label' => 'Invoiced'),

            array('attribute_code' => 'refunded',
                'is_visible' => 0,
                'on_print' => 1,
                'frontend_label' => 'Refunded'),

        );
        return $orderAttr;

    }


    public function getOrderIds()
    {
        set_time_limit(-1);

        $result = array();
        /** @var AW_Aheadmetrics_Model_Resource_Change_Collection $collection */
        $collection = Mage::getModel('awaheadmetrics/change')->getCollection();
        $collection->addFieldToFilter('report_code', array('eq' => self::REPORT_CODE));
        $collection->joinSalesOrder();

        foreach ($collection as $item) {
            /** @var AW_Aheadmetrics_Model_Change $item */
            $result[] = $item->getOrderId();
            $item->delete();
        }

        $result = array_unique($result);
        return $result;

    }

    public function processing()
    {
        $authKey = Mage::helper('awaheadmetrics/config')->getSecurityAuthKey();
        $fullSync = Mage::helper('awaheadmetrics/config')->getFullsyncSalesReport();
        Mage::helper('awaheadmetrics/config')->setFullsyncSalesReport(false);
        $orderIds=array();
        if(!$fullSync && !count($orderIds=$this->getOrderIds())){
            return ;
        }

        // Product attributes
        $response = '';


        $data = array('auth_key' => $authKey,
            'data' => serialize(array_merge($this->getAttributes(), $this->addOrderAttributes())),
            'report_code' => self::REPORT_CODE,
            'type' => 'send_attributes');
        try {
            $this->_sendRequest('setattributes', $data, $response);
//            echo $response;
        } catch (Exception $e) {
            AW_Aheadmetrics_Helper_Data::logE($e);
            return;
        }

        $data = array('auth_key' => $authKey,
            'report_code' => self::REPORT_CODE,
            'type' => 'send_data');


        $collection = Mage::getModel('sales/order')->getCollection();

        if (!$fullSync) {
            $collection->addFieldToFilter('entity_id', array("in" => $orderIds));
        }

        $count=0;
        foreach ($collection as $order) { //sales/order'
            $items = $order->getAllVisibleItems();
            $orderSendData = array();

            foreach ($items as $orderItem) { //sales/order_item
                $tempData = array(
                    'salesAttributes' => array(
                        'date' => $order->getData('created_at'),
                        'store_id' => $order->getData('store_id'),
                        'order_state' => $order->getData('state'),
                        'order_status' => $order->getData('status'),
                        'qty_ordered' => $orderItem->getData('qty_ordered'),
                        'subtotal' => $orderItem->getData('base_row_total'),
                        'tax' => $orderItem->getData('base_tax_invoiced'),
                        'total' => $orderItem->getData('base_row_total'),
                        'discount_amount' => $orderItem->getData('base_discount_amount'),
                        'invoiced' => $orderItem->getData('base_row_invoiced'),
                        'refunded' => $orderItem->getData('base_amount_refunded'),
                    ),
                );


                $productModel = Mage::getModel('catalog/product')->load($orderItem->getProductId());
                $attributes = $productModel->getAttributes();
                //get only frontend attributes of a product
                $attributesArray = array();
                foreach ($attributes as $attribute) {

                    $attributesArray[$attribute->getAttributeCode()] = $attribute->getFrontend()->getValue($productModel);
                }
                $tempData['productAttributes'] = $attributesArray;

                $orderSendData[$orderItem->getId()] = array_merge($tempData['salesAttributes'], $tempData['productAttributes']);
            }

            $count++;
            $data['data'] = serialize($orderSendData);
            $data['count']=$count;
            try {
                $this->_sendRequest('setData', $data, $response);
  //              echo  $response;
            } catch (Exception $e) {
                AW_Aheadmetrics_Helper_Data::logE($e);
                return;
            }
            unset($orderSendData);

        }
        try {
            $data = array('auth_key' => $authKey,
                'report_code' => self::REPORT_CODE,
                'count' => $count,
                'type' => 'end_data');

            $this->_sendRequest('endData', $data, $response);
            //              echo  $response;
        } catch (Exception $e) {
            AW_Aheadmetrics_Helper_Data::logE($e);
            return;
        }


    }

}