<?php
class AW_Aheadmetrics_Model_Reports_Customerreport extends AW_Aheadmetrics_Model_Reports_Abstract
{
    const REPORT_CODE = 'SalesByCustomerAttributesReport';

    private function getCountriesArray()
    {
        $countriesCollection = Mage::getModel('directory/country')->getResourceCollection();
        $countries = array();
        foreach ($countriesCollection as $item) {
            $name = Mage::app()->getLocale()->getCountryTranslation($item->getData('country_id'));
            $countries[$item->getData('country_id')] = $name;
        }
        asort($countries);
        return $countries;
    }

    private function getAttributes()
    {
        $countries = array();
        foreach ($this->getCountriesArray() as $key => $value) {
            $countries[] = array('value' => $key, 'label' => $value);
        }
        $customerGroups = array();
        $customerGroupsModel = Mage::getModel('customer/group')->getCollection();
        foreach ($customerGroupsModel as $item) {
            $id = $item->getData('customer_group_id');
            $code = $item->getData('customer_group_code');
            $customerGroups[] = array('value' => $id, 'label' => $code);
        }


        $customerAttr = array(
            array('attribute_code' => 'billing_region',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'text',
                'frontend_label' => 'State/Province'),

            array('attribute_code' => 'billing_postcode',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'text',
                'frontend_label' => 'ZIP code'),
            array('attribute_code' => 'billing_city',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'text',
                'frontend_label' => 'City'),
            array('attribute_code' => 'billing_street',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'text',
                'frontend_label' => 'Street Address'),


            array('attribute_code' => 'billing_email',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'string',
                'frontend_label' => 'Email'),
            array('attribute_code' => 'billing_country',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'select',
                'frontend_label' => 'Country',
                'values' => $countries
            ),

            array('attribute_code' => 'billing_firstname',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'text',
                'frontend_label' => 'First name'),
            array('attribute_code' => 'billing_lastname',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'text',
                'frontend_label' => 'Last name'),
            array('attribute_code' => 'shipping_region',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'text',
                'frontend_label' => 'State/Province'),
            array('attribute_code' => 'shipping_postcode',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'text',
                'frontend_label' => 'ZIP code'),
            array('attribute_code' => 'shipping_city',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'text',
                'frontend_label' => 'City'),
            array('attribute_code' => 'shipping_street',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'text',
                'frontend_label' => 'Street Address'),
            array('attribute_code' => 'shipping_email',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'string',
                'frontend_label' => 'Email'),
            array('attribute_code' => 'shipping_country',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'select',
                'frontend_label' => 'Country',
                'values' => $countries
            ),
            array('attribute_code' => 'shipping_firstname',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'text',
                'frontend_label' => 'First name'),
            array('attribute_code' => 'shipping_lastname',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'text',
                'frontend_label' => 'Last name'),
            array('attribute_code' => 'dob',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'age',
                'frontend_label' => 'Day of birth'),
            array('attribute_code' => 'gender',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'select',
                'frontend_label' => 'Gender',
                'values' => array(
                    array(
                        'value' => '0',
                        'label' => 'Not set',
                    ),
                    array(
                        'value' => '1',
                        'label' => 'Male',
                    ),
                    array(
                        'value' => '2',
                        'label' => 'Female',
                    ),
                )),
            array('attribute_code' => 'customer_group',
                'is_visible' => 1,
                'on_print' => 0,
                'frontend_input' => 'select',
                'frontend_label' => 'Customer Group',
                'values' => $customerGroups),
        );

        return $customerAttr;
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

            array('attribute_code' => 'shipping',
                'is_visible' => 0,
                'on_print' => 1,
                'frontend_label' => 'Shipping Amount'),

            array('attribute_code' => 'discount_amount',
                'is_visible' => 0,
                'on_print' => 0,
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
        $result = array();
        /** @var AW_Aheadmetrics_Model_Resource_Change_Collection $collection */
        $collection = Mage::getModel('awaheadmetrics/change')->getCollection();
        $collection->addFieldToFilter('report_code', array('eq' => self::REPORT_CODE));
        foreach ($collection as $item) {
            /** @var AW_Aheadmetrics_Model_Change $item */

            $result[] = $item->getId();
            $item->delete();
        }

        $result = array_unique($result);
        return $result;

    }

    public function processing()
    {
        set_time_limit(-1);

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(-1);

        $authKey = Mage::helper('awaheadmetrics/config')->getSecurityAuthKey();
        $fullSync = Mage::helper('awaheadmetrics/config')->getFullsyncCustomerReport();
        Mage::helper('awaheadmetrics/config')->setFullsyncCustomerReport(false);

        $orderIds = array();
        if (!$fullSync && !count($orderIds = $this->getOrderIds())) {
            return;
        }

        // Product attributes

        $data = array('auth_key' => $authKey, //'51e545107940a5.43771192',
            'data' => serialize(array_merge($this->getAttributes(), $this->addOrderAttributes())),
            'report_code' => self::REPORT_CODE,
            'type' => 'send_attributes');
        try {
            $this->_sendRequest('setattributes', $data, $response);
            //    echo($response);
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
        $count = 0;
        foreach ($collection as $order) { //sales/order'
            $tempData = array(
                'salesAttributes' => array(
                    'date' => $order->getData('created_at'),
                    'store_id' => $order->getData('store_id'),
                    'order_state' => $order->getData('state'),
                    'order_status' => $order->getData('status'),
                    'qty_ordered' => (float)$order->getData('total_qty_ordered'),
                    'subtotal' => (float)$order->getData('base_subtotal'),
                    'tax' => (float)$order->getData('base_tax_amount'),
                    'total' => (float)$order->getData('base_grand_total'),
                    'shipping' => (float)$order->getData('base_shipping_amount'),
                    'discount_amount' => (float)$order->getData('base_discount_amount'),
                    'invoiced' => (float)$order->getData('base_total_invoiced'),
                    'refunded' => (float)$order->getData('base_total_refunded'),
                ),
            );
            $billingAddress = $order->getBillingAddress();
            $shippingAddress = $order->getShippingAddress();

            // $countriesArray = $this->getCountriesArray();

            $tempData['customerAttributes']['gender'] = (int)$order->getCustomerGender();

            if (in_array($tempData['customerAttributes']['gender'], array('123', '124'))
            ) {
                $tempData['customerAttributes']['gender'] =
                    ($tempData['customerAttributes']['gender'] == '123' ? '1' : '2');
            }

            $tempData['customerAttributes']['dob'] = $order->getCustomerDob();

            $tempData['customerAttributes']['customer_group'] = $order->getCustomerGroupId(); //Mage::getModel('customer/group')->load($order->getCustomerGroupId())->getCode();
            $tempData['customerAttributes']['customer_is_guest'] = (is_null($order->getCustomerId()) ? true : false);

            if ($billingAddress instanceof Mage_Sales_Model_Order_Address) {
                $tempData['customerAttributes']['billing_region'] = $billingAddress->getRegion();
                $tempData['customerAttributes']['billing_postcode'] = $billingAddress->getPostcode();
                $tempData['customerAttributes']['billing_city'] = $billingAddress->getCity();
                $tempData['customerAttributes']['billing_street'] = $billingAddress->getStreet();
                $tempData['customerAttributes']['billing_email'] = $billingAddress->getEmail();
                $tempData['customerAttributes']['billing_country'] = $billingAddress->getCountryId(); //(isset($countriesArray[$billingAddress->getCountryId()]) ? $countriesArray[$billingAddress->getCountryId()] : '');
                $tempData['customerAttributes']['billing_firstname'] = $billingAddress->getFirstname();
                $tempData['customerAttributes']['billing_lastname'] = $billingAddress->getLastname();
            }
            if ($shippingAddress instanceof Mage_Sales_Model_Order_Address) { //Mage_Sales_Model_Order_Address
                $tempData['customerAttributes']['shipping_region'] = $shippingAddress->getData('region');
                $tempData['customerAttributes']['shipping_postcode'] = $shippingAddress->getPostcode();
                $tempData['customerAttributes']['shipping_city'] = $shippingAddress->getCity();
                $tempData['customerAttributes']['shipping_street'] = $shippingAddress->getStreet();
                $tempData['customerAttributes']['shipping_email'] = $shippingAddress->getEmail();
                $tempData['customerAttributes']['shipping_country'] = $shippingAddress->getCountryId(); //(isset($countriesArray[$shippingAddress->getCountryId()]) ? $countriesArray[$shippingAddress->getCountryId()] : '');
                $tempData['customerAttributes']['shipping_firstname'] = $shippingAddress->getFirstname();
                $tempData['customerAttributes']['shipping_lastname'] = $shippingAddress->getLastname();
            }


            $orderSendData[$order->getId()] = array_merge($tempData['salesAttributes'], $tempData['customerAttributes']);
            $count++;
            $data['data'] = serialize($orderSendData);
            $data['count'] = $count;
            try {
                $this->_sendRequest('setData', $data, $response);
                //echo $response;
            } catch (Exception $e) {
                AW_Aheadmetrics_Helper_Data::logE($e);
                return;
            }
            unset($orderSendData[$order->getId()]);
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