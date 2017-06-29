<?php

class Nick_Trackingimport_GridController extends Mage_Adminhtml_Controller_Action {

     protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/trackingimport/')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Sales'), Mage::helper('adminhtml')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Import Tacking'), Mage::helper('adminhtml')->__('Import Tracking'))
        ;
        return $this;
    }

    /**
     * Overview page
     */
   public function GridAction()
    {
         $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('trackingimport/grid_grid'))
            ->renderLayout();
    }
	
	public function viewTrackAction()
    {
         $trackId    = $this->getRequest()->getParam('track_id');
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $track = Mage::getModel('sales/order_shipment_track')->load($trackId);
        if ($track->getId()) {
            try {
                $response = $track->getNumberDetail();
            }
            catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Can not retrieve tracking number detail.'),
                );
            }
        }
        else {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Can not load track with retrieving identifier.'),
            );
        }

        if ( is_object($response)){
            $className = Mage::getConfig()->getBlockClassName('adminhtml/template');
            $block = new $className();
            $block->setType('adminhtml/template')
                ->setIsAnonymous(true)
                ->setTemplate('sales/order/shipment/tracking/info.phtml');

            $block->setTrackingInfo($response);

            $this->getResponse()->setBody($block->toHtml());
        }
        else {
            if (is_array($response)) {
                $response = Zend_Json::encode($response);
            }

            $this->getResponse()->setBody($response);
        }
    }
	
	
	public function addTrackAction()
    {
        try {
            $Id = $this->getRequest()->getParam('shipment_id');
			$carrier = $this->getRequest()->getPost('carrier');
            $number  = $this->getRequest()->getPost('number');
            $title  = $this->getRequest()->getPost('title');
            if (empty($carrier)) {
                Mage::throwException($this->__('You need specify carrier.'));
            }
            if (empty($number)) {
                Mage::throwException($this->__('Tracking number can not be empty.'));
            }
            if ($shipment = Mage::getModel('sales/order_shipment')->load($Id)) {
                $track = Mage::getModel('sales/order_shipment_track')
                    ->setNumber($number)
                    ->setCarrierCode($carrier)
                    ->setTitle($title);
                $shipment->addTrack($track)
                    ->save();
					
			     $this->loadLayout();
				 
                $response ='Tracking Was Successfully Added To Shipment';

            }
            else {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Can not initialize shipment for adding tracking number. '.$Id.''),
                );
            }
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
                'message'   => $this->__('Can not add tracking number.'),
            );
        }
        if (is_array($response)) {
            $response = Zend_Json::encode($response);
        }
        $this->getResponse()->setBody($response);
    }

    public function removeTrackAction()
    {
        $trackId    = $this->getRequest()->getParam('track_id');
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $track = Mage::getModel('sales/order_shipment_track')->load($trackId);
        if ($track->getId()) {
            try {
                if ($shipmentId = $this->_initShipment()) {
                    $track->delete();

                    $this->loadLayout();
                    $response = 'Tracking Was Successfully Deleted From Shipment';
                }
                else {
                    $response = array(
                        'error'     => true,
                        'message'   => $this->__('Can not initialize shipment for delete tracking number.'),
                    );
                }
            }
            catch (Exception $e) {
                $response = array(
                    'error'     => true,
                    'message'   => $this->__('Can not delete tracking number.'),
                );
            }
        }
        else {
            $response = array(
                'error'     => true,
                'message'   => $this->__('Can not load track with retrieving identifier.'),
            );
        }
        if (is_array($response)) {
            $response = Zend_Json::encode($response);
        }
        $this->getResponse()->setBody($response);
    }
	
	
    public function DispatchOrderAction() {
	
		 $orderids = $this->getRequest()->getParam('order_ids', array());
		
		if ($orderids > 0) {
			 foreach ($orderids as $orderid) {
				 $order = Mage::getModel('sales/order')->load($orderid);
				 $order->setStatus('shipped');
				 $order->addStatusToHistory('shipped', 'Dispatched', true);
				 $order->save();
				 $shipment = Mage::getModel('sales/order_shipment')->load($orderid);
				 if ($shipment)    $shipment->sendUpdateEmail(true, false);
			}
			$this->_getSession()->addSuccess($this->__(count($orderids).' Order Status Update To Dispatched '));
		}
		$this->_redirect('trackingimport/grid/grid' , array());
	}
}