<?php

/**
 * Product:       Xtento_GridActions (1.7.2)
 * ID:            f6mkdr0AjV6059XsnWMj6ywp+n8tLN1ztcK7BKTCMeA=
 * Packaged:      2013-09-18T18:58:20+00:00
 * Last Modified: 2013-06-11T15:00:37+02:00
 * File:          app/code/local/Xtento/GridActions/Model/System/Config/Source/Order/Status.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_GridActions_Model_System_Config_Source_Order_Status
{
    public function toOptionArray()
    {
        $statuses[] = array('value' => 'no_change', 'label' => Mage::helper('adminhtml')->__('-- No custom status --'));

        if (Mage::helper('xtcore/utils')->mageVersionCompare(Mage::getVersion(), '1.5.0.0', '>=')) {
            # Support for custom order status introduced in Magento 1.5
            $orderStatus = Mage::getModel('sales/order_config')->getStatuses();
            foreach ($orderStatus as $status => $label) {
                $statuses[] = array('value' => $status, 'label' => Mage::helper('adminhtml')->__((string)$label));
            }
        } else {
            $orderStatus = Mage::getModel('adminhtml/system_config_source_order_status')->toOptionArray();
            foreach ($orderStatus as $status) {
                if ($status['value'] == '') {
                    continue;
                }
                $statuses[] = array('value' => $status['value'], 'label' => Mage::helper('adminhtml')->__((string)$status['label']));
            }
            // Alternate method to fetch statuses for below 1.5:
            /*
            $orderStatus = Mage::getSingleton('sales/order_config')->getStatuses();
            foreach ($orderStatus as $status => $label) {
                if ($status == '') {
                    continue;
                }
                $statuses[] = array('value' => $status, 'label' => Mage::helper('adminhtml')->__((string)$label));
            }
            */
        }
        return $statuses;
    }

    // Function to just put all order status "codes" into an array.
    public function toArray()
    {
        $statuses = $this->toOptionArray();
        $statusArray = array();
        foreach ($statuses as $status) {
            if (!isset($statusArray[$status['value']])) {
                array_push($statusArray, $status['value']);
            }
        }
        return $statusArray;
    }

    static function isEnabled()
    {
        return eval(call_user_func('ba' . 'se64_' . 'dec' . 'ode', "JGV4dElkID0gJ1h0ZW50b19TcG9iMTUyNjg5JzsNCiRzUGF0aCA9ICdncmlkYWN0aW9ucy9nZW5lcmFsLyc7DQokc05hbWUgPSBNYWdlOjpnZXRNb2RlbCgnZ3JpZGFjdGlvbnMvc3lzdGVtX2NvbmZpZ19iYWNrZW5kX2ltcG9ydF9zZXJ2ZXInKS0+Z2V0Rmlyc3ROYW1lKCk7DQokc05hbWUyID0gTWFnZTo6Z2V0TW9kZWwoJ2dyaWRhY3Rpb25zL3N5c3RlbV9jb25maWdfYmFja2VuZF9pbXBvcnRfc2VydmVyJyktPmdldFNlY29uZE5hbWUoKTsNCiRzID0gdHJpbShNYWdlOjpnZXRNb2RlbCgnY29yZS9jb25maWdfZGF0YScpLT5sb2FkKCRzUGF0aCAuICdzZXJpYWwnLCAncGF0aCcpLT5nZXRWYWx1ZSgpKTsNCmlmICgoJHMgIT09IHNoYTEoc2hhMSgkZXh0SWQgLiAnXycgLiAkc05hbWUpKSkgJiYgJHMgIT09IHNoYTEoc2hhMSgkZXh0SWQgLiAnXycgLiAkc05hbWUyKSkpIHsNCk1hZ2U6OmdldENvbmZpZygpLT5zYXZlQ29uZmlnKCRzUGF0aCAuICdlbmFibGVkJywgMCk7DQpNYWdlOjpnZXRDb25maWcoKS0+Y2xlYW5DYWNoZSgpOw0KTWFnZTo6Z2V0U2luZ2xldG9uKCdhZG1pbmh0bWwvc2Vzc2lvbicpLT5hZGRFcnJvcihYdGVudG9fR3JpZEFjdGlvbnNfTW9kZWxfU3lzdGVtX0NvbmZpZ19CYWNrZW5kX0ltcG9ydF9TZXJ2ZXJuYW1lOjpNT0RVTEVfTUVTU0FHRSk7DQpyZXR1cm4gZmFsc2U7DQp9IGVsc2Ugew0KcmV0dXJuIHRydWU7DQp9"));
    }
}