<?php

class Nick_Trackingimport_ImportController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Show Main Grid
     *
     */
      protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sales/trackingimport')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Sales'), Mage::helper('adminhtml')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Import Tacking'), Mage::helper('adminhtml')->__('Import Tracking'))
        ;
        return $this;
    }

    /**
     * Overview page
     */
   public function indexAction()
    {
         $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('trackingimport/import'))
            ->renderLayout();
    }

    
	public function exportorderPostAction()
    {
        /** start csv content and set template */
        $headers = new Varien_Object(array(
            'orderno'       => Mage::getStoreConfig('trackingimport/csvheaders/orderid'),
            'shipmentno' 	=> Mage::getStoreConfig('trackingimport/csvheaders/shipmentid'),
            'carrier'  		=> Mage::getStoreConfig('trackingimport/csvheaders/carrierid'),
        ));
        
		$template = '"{{orderno}}","{{shipmentno}}","{{carrier}}"';
        $content = $headers->toString($template);

       $orders = Mage::getModel('sales/order')->getCollection()
            ->addAttributeToFilter("state", Mage_Sales_Model_Order::STATE_PROCESSING)->load();

        $content .= "\n";

		foreach ($orders as $order) {
			$orderStatus = $order->getStatus();
			
			if (Mage::getStoreConfig('trackingimport/general/idselect')){
			
				$invoices = Mage::getResourceModel('sales/order_invoice_collection')
						->addAttributeToSelect('*')
						->setOrderFilter($order->getId());
			
				foreach($invoices as $invoice){		
					$content .=  $invoice->getIncrementId().", , ,"."\n";
				}
			
			} else {
				$content .=  $order->getRealOrderId().", , ,"."\n";
			}
		}	
		
        $this->_prepareDownloadResponse('current_orders.csv', $content);
    }
	
	
	
    public function trackingimportPostAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_tracking_file']['tmp_name'])) {
            try {
                $this->_importTracking();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('trackingimport')->__('Tracking was successfully imported'));
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError('Failed To Import File');
        }
        $this->_redirect('*/*');
    }
	
	
	
	 protected function _importTracking() 
    
    {
		
		$fileName   = $_FILES['import_tracking_file']['tmp_name'];
        $csvObject  = new Varien_File_Csv();
		$csvObject->setDelimiter(Mage::getStoreConfig('trackingimport/general/delimiter'));
		$csvObject->setEnclosure(Mage::getStoreConfig('trackingimport/general/enclosure'));
        $csvData = $csvObject->getData($fileName);

        /** checks columns */
        $csvFields  = array(
            0   => Mage::getStoreConfig('trackingimport/csvheaders/orderid'),
            1   => Mage::getStoreConfig('trackingimport/csvheaders/shipmentid'),
			2   => Mage::getStoreConfig('trackingimport/csvheaders/carrierid'),
        );
		
		foreach ($csvData as $k => $v) {
				
				$orderId = $v[0];
				$trackingNum = $v[1];
				$carrierTitle = $v[2];
			
			if (Mage::getStoreConfig('trackingimport/general/skip') == 1){	
						if ($k == 0) {
							continue;
						}
					}

			try {
				Mage::getModel('Trackingimport/import')->BeginImport($orderId, $trackingNum, $carrierTitle);
			} catch (Mage_Core_Exception $e) {
				//Log Error
			    Mage::log("$e->getMessage()");
				//Email NeXt IT
				$bodyString = "Import failed on order ID: ".$orderId;
				$mail = Mage::getModel('core/email');
				$mail->setToName('Mikel Bitson');
				$mail->setToEmail('mbitson@next-it.net');
				$mail->setBody($bodyString);
				$mail->setSubject($bodyString);
				$mail->setFromEmail('info@hackleyhealthmanagement.com');
				$mail->setFromName("Hackley Health Management");
				$mail->setType('text');// YOu can use Html or text as Mail format
				$mail->send();
			}
		} 
     return;
	}
}
	
