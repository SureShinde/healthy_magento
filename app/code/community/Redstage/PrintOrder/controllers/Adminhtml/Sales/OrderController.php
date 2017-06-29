<?php

require_once("Mage/Adminhtml/controllers/Sales/OrderController.php");

class Redstage_PrintOrder_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{

    // Check order view availability
    protected function _canViewOrder($order) {                
        if (Mage::helper('orderspro')->isHideDeletedOrdersForCustomers()) {
            $orderItemGroup = Mage::getModel('orderspro/order_item_group')->load($order->getId(), 'order_id');
            if ($orderItemGroup->getOrderGroupId()==2) return false;
        }        
        
        return parent::_canViewOrder($order);
    }  
    
    public function pdfOrdersAction(){

        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $orders = Mage::getResourceModel('sales/order_collection')
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id', $orderId)
                    ->load();
                if ($orders->getSize() > 0) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('printorder/sales_order_pdf_order')->getPdf($orders);
                    } else {
                        $pages = Mage::getModel('printorder/sales_order_pdf_order')->getPdf($orders);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'order'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
                    'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }

    public function pdfdocsAction(){
        $orderIds = $this->getRequest()->getPost('order_ids');
        $flag = false;
        if (!empty($orderIds)) {
            foreach ($orderIds as $orderId) {
                $orders = Mage::getResourceModel('sales/order_collection')
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id', $orderId)
                    ->load();
                if ($orders->getSize() > 0) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('printorder/sales_order_pdf_order')->getPdf($orders);
                    } else {
                        $pages = Mage::getModel('printorder/sales_order_pdf_order')->getPdf($orders);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
                
                $invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($invoices->getSize()){
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_invoice')->getPdf($invoices);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }

                $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($shipments->getSize()){
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_shipment')->getPdf($shipments);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }

                $creditmemos = Mage::getResourceModel('sales/order_creditmemo_collection')
                    ->setOrderFilter($orderId)
                    ->load();
                if ($creditmemos->getSize()) {
                    $flag = true;
                    if (!isset($pdf)){
                        $pdf = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                    } else {
                        $pages = Mage::getModel('sales/order_pdf_creditmemo')->getPdf($creditmemos);
                        $pdf->pages = array_merge ($pdf->pages, $pages->pages);
                    }
                }
            }
            if ($flag) {
                return $this->_prepareDownloadResponse(
                    'docs'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf',
                    $pdf->render(), 'application/pdf'
                );
            } else {
                $this->_getSession()->addError($this->__('There are no printable documents related to selected orders.'));
                $this->_redirect('*/*/');
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function massArchiveAction() {        
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $count = Mage::helper('orderspro')->addToOrderGroup($orderIds, 1);
        if ($count>0) Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('orderspro')->__('Selected orders were archived.'));
        $this->_redirect('*/*/');                
    }    
    
    public function massDeleteAction() {        
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $count = Mage::helper('orderspro')->addToOrderGroup($orderIds, 2);
        if ($count>0) Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('orderspro')->__('Selected orders were deleted.'));
        $this->_redirect('*/*/');                
    }
    
    public function massDeleteCompletelyAction() {        
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        if (!$orderIds) {
            $orderId = $this->getRequest()->getParam('order_id', false);
            if ($orderId) $orderIds = array($orderId);
        }
        if ($orderIds) {
            $count = Mage::helper('orderspro')->deleteOrderCompletely($orderIds);
            if ($count==1) Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('orderspro')->__('Order has been completely deleted.'));
            if ($count>1) Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('orderspro')->__('Selected orders were completely deleted.'));
        }
        $this->_redirect('*/*/');                
    }        
    
    
    public function massRestoreAction() {        
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $count = Mage::helper('orderspro')->addToOrderGroup($orderIds, 0);
        if ($count>0) Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('orderspro')->__('Selected orders were restored.'));
        $this->_redirect('*/*/');                
    }
    
    public function massInvoiceAction() {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $count = Mage::helper('orderspro')->invoiceOrder($orderIds);
        if ($count>0) Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('orderspro')->__('Selected orders were invoiced.'));
        $this->_redirect('*/*/');
    }
    
    public function massShipAction() {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $count = Mage::helper('orderspro')->shipOrder($orderIds);
        if ($count>0) Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('orderspro')->__('Selected orders were shipped.'));
        $this->_redirect('*/*/');
    }
    
    public function massInvoiceAndShipAction() {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $count = Mage::helper('orderspro')->invoiceOrder($orderIds);
        $count += Mage::helper('orderspro')->shipOrder($orderIds);
        if ($count>0) Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('orderspro')->__('Selected orders were invoiced and shipped.'));
        $this->_redirect('*/*/');
    }
    
//    public function massManufactureAction()
//    {
//        $orderIds = $this->getRequest()->getPost('order_ids', array());
//        $count = Mage::helper('orderspro')->changeStatusOrder($orderIds, 'manufacturing', '', 1, false);
//        if ($count>0) Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('orderspro')->__('Selected orders were manufactured.'));
//        $this->_redirect('*/*/');
//    }
    /**
     * Add order comment action
     */
    public function addCommentAction() {                
        
        if ($order = $this->_initOrder()) {
            try {
                $response = false;
                $data = $this->getRequest()->getPost('history');
                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;

                $order->addStatusHistoryComment($data['comment'], $data['status'])
                    ->setIsVisibleOnFront($visible)
                    ->setIsCustomerNotified($notify);

                $comment = trim(strip_tags($data['comment']));

                $order->save();
                                
                
                // if send upload file
                if (isset($_FILES['send_file']['size']) && $_FILES['send_file']['size']>0) {
                
                    $histories = $order->getStatusHistoryCollection(true);
                    foreach ($histories as $h) {
                        $historyId =$h->getEntityId();
                        break;                    
                    }
                                        
                    
                    $uploadFile = Mage::getModel('orderspro/upload_files')
                            ->setHistoryId($historyId)
                            ->setFileName($_FILES['send_file']['name'])
                            ->setFileSize($_FILES['send_file']['size'])                            
                            ->save();                    
                    
                    $fileId = $uploadFile->getEntityId();
                    $filePath = Mage::helper('orderspro')->getUploadFilesPath($fileId, true);
                    copy($_FILES['send_file']['tmp_name'], $filePath);
                    
                    Mage::helper('orderspro')->sendOrderUpdateEmail($order, $notify, $comment, $filePath, $uploadFile->getFileName());
                    return $this->_redirectReferer();                   
                }
                
                $order->sendOrderUpdateEmail($notify, $comment);

                $this->loadLayout('empty');
                $this->renderLayout();
            }
            catch (Mage_Core_Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $e->getMessage(),
                );
            }
            catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Cannot add order history.')
                );
            }
            if (is_array($response)) {
                $response = Mage::helper('core')->jsonEncode($response);
                $this->getResponse()->setBody($response);
            }
        }
    }
    
    
    public function deleteHistoryAction() {
        try {            
            $response = false;
            $id = $this->getRequest()->getParam('id');
            
            Mage::getModel('orderspro/upload_files')->load($id, 'history_id')->removeFile();            
            Mage::getModel('sales/order_status_history')->load($id)->delete();
            
            $order = $this->_initOrder();
            $this->loadLayout('empty');
            $this->renderLayout();
            
        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => $e->getMessage(),
            );
        } catch (Exception $e) {
            $response = array(
                'error'     => true,
                'message'   => Mage::helper('orderspro')->__('Failed to remove item.')
            );
        }
            
        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
            $this->getResponse()->setBody($response);
        }        
    }
    
    public function deleteInvoiceAndShipmentAction() {
        $orderId = intval($this->getRequest()->getParam('order_id'));
        if ($orderId>0) {
            $coreResource = Mage::getSingleton('core/resource');
            $write = $coreResource->getConnection('core_write');            
            $write->query("DELETE FROM `".$coreResource->getTableName('sales_flat_shipment')."` WHERE `order_id` = " . $orderId);
            $write->query("DELETE FROM `".$coreResource->getTableName('sales_flat_shipment_grid')."` WHERE `order_id` = " . $orderId);
            
            $write->query("DELETE FROM `".$coreResource->getTableName('sales_flat_invoice')."` WHERE `order_id` = " . $orderId);
            $write->query("DELETE FROM `".$coreResource->getTableName('sales_flat_invoice_grid')."` WHERE `order_id` = " . $orderId);
            
            $write->query("UPDATE `".$coreResource->getTableName('sales_flat_order_item')."` SET `qty_invoiced` = 0, `qty_shipped` = 0 WHERE `order_id` = ".$orderId);            
            $write->query("UPDATE `".$coreResource->getTableName('sales_flat_order')."` SET `shipping_invoiced` = 0, `base_shipping_invoiced` = 0 WHERE `entity_id` = ".$orderId);
        }
        $this->getResponse()->setBody('ok');
    }

}
