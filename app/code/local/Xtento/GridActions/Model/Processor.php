<?php

/**
 * Product:       Xtento_GridActions (1.7.2)
 * ID:            f6mkdr0AjV6059XsnWMj6ywp+n8tLN1ztcK7BKTCMeA=
 * Packaged:      2013-09-18T18:58:20+00:00
 * Last Modified: 2013-09-04T14:20:26+02:00
 * File:          app/code/local/Xtento/GridActions/Model/Processor.php
 * Copyright:     Copyright (c) 2013 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

class Xtento_GridActions_Model_Processor extends Mage_Core_Model_Abstract
{
    private $_statuses;

    /*
    * Process orders
    */
    public function processOrders()
    {
        set_time_limit(0);
        if (!Mage::helper('gridactions/data')->getModuleEnabled()) {
            return $this;
        }
        $orderIds = Mage::app()->getRequest()->getParam('order_ids');
        $actionsToRun = Mage::app()->getRequest()->getParam('actions');
        if (!is_array($orderIds) || empty($actionsToRun)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('gridactions')->__('Please select order(s) and actions to run on orders.'));
            return $this;
        }
        if (!Xtento_GridActions_Model_System_Config_Source_Order_Status::isEnabled()) {
            return $this;
        }

        $tracksAndCarriers = array();

        // Order status modifications
        if (!isset($this->_statuses)) {
            $this->_statuses = Mage::getSingleton('gridactions/system_config_source_order_status')->toArray();
        }
        $completeStatus = Mage::getStoreConfig('gridactions/general/change_status_complete');
        if ($completeStatus == '') {
            $completeStatus = 'no_change';
        } else if (!in_array($completeStatus, $this->_statuses) && $completeStatus !== 'no_change') {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('gridactions')->__('The custom order status which should be set for an order after completing it does not exist anymore. Please make sure you set a valid custom order status at System > Configuration > XTENTO Extensions > Simplify Bulk Order Processing. Processing stopped.'));
            return $this;
        }
        $shipStatus = Mage::getStoreConfig('gridactions/general/change_status_ship');
        if ($shipStatus == '') {
            $shipStatus = 'no_change';
        } else if (!in_array($shipStatus, $this->_statuses) && $shipStatus !== 'no_change') {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('gridactions')->__('The custom order status which should be set for an order after shipping it does not exist anymore. Please make sure you set a valid custom order status at System > Configuration > XTENTO Extensions > Simplify Bulk Order Processing. Processing stopped.'));
            return $this;
        }
        $invoiceStatus = Mage::getStoreConfig('gridactions/general/change_status_invoice');
        if ($invoiceStatus == '') {
            $invoiceStatus = 'no_change';
        } else if (!in_array($invoiceStatus, $this->_statuses) && $invoiceStatus !== 'no_change') {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('gridactions')->__('The custom order status which should be set for an order after invoicing it does not exist anymore. Please make sure you set a valid custom order status at System > Configuration > XTENTO Extensions > Simplify Bulk Order Processing. Processing stopped.'));
            return $this;
        }

        // Defined actions:
        $doInvoice = false;
        $doForceCapture = false;
        $doShip = false;
        $doComplete = false;
        $doDelete = false;
        $doNotify = false;
        $doPrintDocuments = false;
        $doChangeStatus = false;
        $doForceEmail = false;

        if (!strstr($actionsToRun, '_setstatus')) {
            if (strstr($actionsToRun, '_invoice')) {
                $this->_excludedCapturePaymentMethods = explode(",", Mage::getStoreConfig('gridactions/general/capture_methods_excluded'));
                $doInvoice = true;
            }
            if (strstr($actionsToRun, '_capture')) {
                $doForceCapture = true;
            }
            if (strstr($actionsToRun, '_ship')) {
                $tracksAndCarriers = $this->_parseTracksAndCarriers();
                $doShip = true;
            }
            if (strstr($actionsToRun, '_complete')) {
                $doComplete = true;
            }
            if (strstr($actionsToRun, '_delete')) {
                $doDelete = true;
            }
            if (strstr($actionsToRun, '_notify')) {
                $doNotify = true;
            }
            if (strstr($actionsToRun, '_print')) {
                $doPrintDocuments = true;
            }
            if (strstr($actionsToRun, '_forcenotification')) {
                $doForceEmail = true;
            }
        } else if (strstr($actionsToRun, '_setstatus')) {
            $doChangeStatus = true;
            $newOrderStatus = str_replace('_setstatus_', '', $actionsToRun);
        }

        // Other settings
        $doCapture = Mage::getStoreConfigFlag('gridactions/general/do_capture');
        $setPaid = Mage::getStoreConfigFlag('gridactions/general/set_paid');

        $modifiedCount = 0;
        foreach ($orderIds as $orderId) {
            try {
                $isModified = false;

                $order = Mage::getModel('sales/order')->load((int)$orderId);
                if (!$order || !$order->getId()) {
                    Mage::getSingleton('adminhtml/session')->addError('Could not modify order with entity_id ' . $orderId . '. Order has been deleted in the meantime?');
                    continue;
                }

                if (($doInvoice || $doShip || $doComplete) && $order->getStatus() == Mage_Sales_Model_Order::STATE_HOLDED) {
                    $order->unhold()->save();
                }

                #Mage::app()->setCurrentStore($order->getStoreId());
                #Mage::app()->getLocale()->emulate($order->getStoreId());

                if ($doInvoice && !$doForceEmail && $order->canInvoice()) {
                    /** @var $invoice Mage_Sales_Model_Order_Invoice */
                    $invoice = $order->prepareInvoice();
                    if ($doCapture && $invoice->canCapture() && $this->_doCapturePaymentMethod((string)$order->getPayment()->getMethod())) {
                        // Capture order online
                        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                    } else if ($setPaid) {
                        // Set invoice status to Paid
                        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                    }
                    $invoice->register();
                    $invoice->setEmailSent($doNotify);

                    $invoice->getOrder()->setIsInProcess(true);

                    $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder())
                        ->save();

                    if ($doNotify) $invoice->sendEmail($doNotify, '');
                    unset($invoice);
                    $isModified = true;
                }
                if ($doInvoice && $doForceEmail) {
                    $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                        ->setOrderFilter($order)
                        ->addAttributeToSelect('entity_id')
                        ->addAttributeToSort('entity_id', 'desc')
                        ->setPage(1, 1);
                    $lastInvoice = $invoices->getFirstItem();
                    if ($lastInvoice->getId()) {
                        $lastInvoice = Mage::getModel('sales/order_invoice')->load($lastInvoice->getId());
                        $lastInvoice->setEmailSent(true);
                        $lastInvoice->sendEmail(true, '');
                        $lastInvoice->save();
                    }
                }

                if ($doForceCapture) {
                    foreach ($order->getInvoiceCollection() as $invoice) {
                        if ($invoice->canCapture()) {
                            $invoice = Mage::getModel('sales/order_invoice')->load($invoice->getId());
                            $invoice->capture();
                            $invoice->getOrder()->setIsInProcess(true);
                            $transactionSave = Mage::getModel('core/resource_transaction')
                                ->addObject($invoice)
                                ->addObject($invoice->getOrder())
                                ->save();
                            $isModified = true;
                        }
                    }
                }

                if ($doShip && !$doForceEmail && !$order->canShip() && isset($tracksAndCarriers[$orderId])) {
                    // Order has been already shipped.. add another tracking number.
                    if (Mage::getStoreConfigFlag('gridactions/general/add_trackingnumber_from_grid_shipped')) {
                        // Add a second/third/whatever tracking number to the shipment - if possible.
                        /* @var $shipments Mage_Sales_Model_Mysql4_Order_Shipment_Collection */
                        $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                            ->setOrderFilter($order)
                            ->addAttributeToSelect('entity_id')
                            ->addAttributeToSort('entity_id', 'desc')
                            ->setPage(1, 1);
                        $lastShipment = $shipments->getFirstItem();
                        if ($lastShipment->getId()) {
                            $lastShipment = Mage::getModel('sales/order_shipment')->load($lastShipment->getId());

                            foreach ($tracksAndCarriers[$orderId] as $trackData) {
                                $trackingNumber = $trackData['tracking_number'];
                                $carrierCode = $trackData['carrier'];
                                $carrierName = Mage::helper('gridactions')->determineCarrierTitle($carrierCode, $order->getShippingDescription());

                                if (empty($carrierCode) && !empty($carrierName)) {
                                    $carrierCode = $carrierName;
                                }
                                if (empty($carrierName) && !empty($carrierCode)) {
                                    $carrierName = $carrierCode;
                                }
                                $trackAlreadyAdded = false;
                                foreach ($lastShipment->getAllTracks() as $trackInfo) {
                                    if (Mage::helper('xtcore/utils')->mageVersionCompare(Mage::getVersion(), '1.6.0.0', '>=')) {
                                        if ($trackInfo->getTrackNumber() == $trackingNumber) {
                                            $trackAlreadyAdded = true;
                                            break;
                                        }
                                    } else {
                                        if ($trackInfo->getNumber() == $trackingNumber) {
                                            $trackAlreadyAdded = true;
                                            break;
                                        }
                                    }
                                }
                                if (!$trackAlreadyAdded) {
                                    if (!empty($trackingNumber)) {
                                        // Determine carrier and add tracking number
                                        $trackingNumber = str_replace("'", "", $trackingNumber);
                                        $track = Mage::getModel('sales/order_shipment_track')
                                            ->setCarrierCode($carrierCode)
                                            ->setTitle($carrierName);

                                        // Starting with Magento CE 1.6 / EE 1.10 Magento renamed the tracking number attribute to track_number.
                                        if (Mage::helper('xtcore/utils')->mageVersionCompare(Mage::getVersion(), '1.6.0.0', '>=')) {
                                            $track->setTrackNumber($trackingNumber);
                                        } else {
                                            $track->setNumber($trackingNumber);
                                        }

                                        $lastShipment->addTrack($track)->save();
                                        $isModified = true;

                                        if ($doNotify) {
                                            $lastShipment->sendEmail(true, '');
                                        }
                                    }
                                }
                                unset($lastShipment);
                            }
                        }
                    }
                }

                if ($doShip && !$doForceEmail && $order->canShip()) {
                    $shipment = $order->prepareShipment();
                    $shipment->register();
                    $shipment->setEmailSent($doNotify);
                    $shipment->getOrder()->setIsInProcess(true);

                    if (isset($tracksAndCarriers[$orderId])) {
                        foreach ($tracksAndCarriers[$orderId] as $trackData) {
                            $trackingNumber = $trackData['tracking_number'];
                            $carrier = $trackData['carrier'];
                            if (!empty($trackingNumber)) {
                                $trackingNumber = str_replace("'", "", $trackingNumber);
                                $track = Mage::getModel('sales/order_shipment_track')
                                    ->setCarrierCode($carrier)
                                    ->setTitle(Mage::helper('gridactions')->determineCarrierTitle($carrier, $order->getShippingDescription()));

                                // Starting with Magento CE 1.6 / EE 1.10 Magento renamed the tracking number attribute to track_number.
                                if (Mage::helper('xtcore/utils')->mageVersionCompare(Mage::getVersion(), '1.6.0.0', '>=')) {
                                    $track->setTrackNumber($trackingNumber);
                                } else {
                                    $track->setNumber($trackingNumber);
                                }

                                $shipment->addTrack($track);
                            }
                        }
                    }

                    $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($shipment)
                        ->addObject($shipment->getOrder())
                        ->save();

                    if ($doNotify) $shipment->sendEmail($doNotify, '');
                    unset($shipment);
                    $isModified = true;
                }

                if ($doShip && $doForceEmail) {
                    $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                        ->setOrderFilter($order)
                        ->addAttributeToSelect('entity_id')
                        ->addAttributeToSort('entity_id', 'desc')
                        ->setPage(1, 1);
                    $lastShipment = $shipments->getFirstItem();
                    if ($lastShipment->getId()) {
                        $lastShipment = Mage::getModel('sales/order_shipment')->load($lastShipment->getId());
                        $lastShipment->setEmailSent(true);
                        $lastShipment->sendEmail(true, '');
                        $lastShipment->save();
                        $isModified = true;
                    }
                }

                $oldStatus = $order->getStatus();
                if ($doComplete && $completeStatus == 'no_change') {
                    $this->_setOrderState($order, Mage_Sales_Model_Order::STATE_COMPLETE);
                    $order->setStatus(Mage_Sales_Model_Order::STATE_COMPLETE);
                    $order->save();
                    $isModified = true;
                } else if ($doComplete && $completeStatus !== 'no_change') {
                    if ($order->getStatus() !== $completeStatus) {
                        $this->_setOrderState($order, $completeStatus);
                        $order->setStatus($completeStatus)->save();
                        $isModified = true;
                    }
                } else if ($doShip && $shipStatus !== 'no_change') {
                    if ($order->getStatus() !== $shipStatus) {
                        $this->_setOrderState($order, $shipStatus);
                        $order->setStatus($shipStatus)->save();
                    }
                } else if ($doInvoice && $invoiceStatus !== 'no_change') {
                    if ($order->getStatus() !== $invoiceStatus) {
                        $this->_setOrderState($order, $invoiceStatus);
                        $order->setStatus($invoiceStatus)->save();
                    }
                } else if ($doChangeStatus && !empty($newOrderStatus)) {
                    #if ($order->getStatus() !== $newOrderStatus) {
                    $this->_setOrderState($order, $newOrderStatus);
                    $order->setStatus($newOrderStatus)->save();
                    #}
                    $isModified = true;
                }
                if ($oldStatus !== $order->getStatus()) {
                    if (Mage::helper('xtcore/utils')->mageVersionCompare(Mage::getVersion(), '1.4.0.0', '>=')) {
                        $order->addStatusHistoryComment('', $order->getStatus())->setIsCustomerNotified(0);
                    } else {
                        // 1.3 compatibility
                        $order->addStatusToHistory($order->getStatus());
                    }

                    // Compatibility fix for Amasty_OrderStatus
                    $statusModel = Mage::registry('amorderstatus_history_status');
                    if (($statusModel && $statusModel->getNotifyByEmail()) || Mage::registry('advancedorderstatus_notifications')) {
                        $order->sendOrderUpdateEmail();
                    }
                    // End
                    $order->save();
                }

                if ($doDelete && ($order->getStatus() === Mage_Sales_Model_Order::STATE_CANCELED && !$order->hasCreditmemos() && !$order->hasShipments() && !$order->hasInvoices())) {
                    Mage::register('isSecureArea', true, true);
                    $order->delete();
                    $isModified = true;
                }

                #Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                #Mage::app()->getLocale()->revert();

                if ($isModified) {
                    $modifiedCount++;
                }
                unset($order);
            } catch (Exception $e) {
                #Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
                #Mage::app()->getLocale()->revert();
                if (isset($order) && $order && $order->getIncrementId()) {
                    $orderId = $order->getIncrementId();
                }
                Mage::getSingleton('adminhtml/session')->addError('Exception (Order # ' . $orderId . '): ' . $e->getMessage());
            }
        }

        if ($doPrintDocuments) {
            if ($doInvoice) {
                Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('gridactions')->__('Click <a href="' . Mage::helper('adminhtml')->getUrl('*/gridactions_print/pdfinvoices', array('order_ids' => implode(",", $orderIds), '_secure' => Mage::app()->getStore()->isCurrentlySecure())) . '" target="_blank">here</a> to print the invoice PDF for processed orders.'));
            }
            if ($doShip) {
                Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('gridactions')->__('Click <a href="' . Mage::helper('adminhtml')->getUrl('*/gridactions_print/pdfshipments', array('order_ids' => implode(",", $orderIds), '_secure' => Mage::app()->getStore()->isCurrentlySecure())) . '" target="_blank">here</a> to print the packingslip PDF for processed orders.'));
                if (Mage::helper('xtcore/utils')->mageVersionCompare(Mage::getVersion(), '1.6.0.0', '>=')) {
                    Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('gridactions')->__('Click <a href="' . Mage::helper('adminhtml')->getUrl('*/gridactions_print/pdflabels', array('order_ids' => implode(",", $orderIds), '_secure' => Mage::app()->getStore()->isCurrentlySecure())) . '" target="_blank">here</a> to print the shipping label PDF for processed orders.'));
                }
            }
        }

        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('gridactions')->__('Total of %d order(s) were modified.', $modifiedCount));

        return $this;
    }

    private function _parseTracksAndCarriers()
    {
        if (!Xtento_GridActions_Model_System_Config_Source_Order_Status::isEnabled()) {
            return array();
        }
        $carriersArray = array();
        $tracksAndCarriers = array();
        $carriers = explode(",", Mage::app()->getRequest()->getPost('carriers', false));
        $tracks = explode(",", Mage::app()->getRequest()->getPost('trackingnumbers', false));
        if (empty($carriers) || empty($tracks)) {
            return array();
        }
        foreach ($carriers as $rawCarrier) {
            if ($rawCarrier == '') {
                continue;
            }
            list($orderId, $carrier) = explode("[|]", $rawCarrier);
            if (!empty($orderId)) {
                $carriersArray[$orderId] = $carrier;
            }
        }
        foreach ($tracks as $rawTrack) {
            if ($rawTrack == '') {
                continue;
            }
            list($orderId, $trackingNumbers) = explode("[|]", $rawTrack);
            if (!empty($orderId)) {
                foreach (explode(";", $trackingNumbers) as $trackingNumber) {
                    $carrier = 'custom';
                    if (isset($carriersArray[$orderId])) {
                        $carrier = $carriersArray[$orderId];
                    }
                    $tracksAndCarriers[$orderId][] = array('carrier' => $carrier, 'tracking_number' => $trackingNumber);
                }
            }
        }
        return $tracksAndCarriers;
    }

    private function _doCapturePaymentMethod($methodCode)
    {
        if (is_array($this->_excludedCapturePaymentMethods) && in_array($methodCode, $this->_excludedCapturePaymentMethods)) {
            return false;
        }
        return true;
    }

    private function _setOrderState($order, $newOrderStatus)
    {
        if (Mage::getStoreConfig('gridactions/general/force_status_change')) {
            return;
        }
        if (Mage::helper('xtcore/utils')->mageVersionCompare(Mage::getVersion(), '1.5.0.0', '>=')) {
            if (!isset($this->_orderStates)) {
                $this->_orderStates = Mage::getModel('sales/order_config')->getStates();
            }
            foreach ($this->_orderStates as $state => $label) {
                foreach (Mage::getModel('sales/order_config')->getStateStatuses($state, false) as $status) {
                    if ($status == $newOrderStatus) {
                        $order->setData('state', $state);
                        return;
                    }
                }
            }
        }
    }
}