<?php

class Redstage_PrintOrder_Model_Sales_Order_Pdf_Order extends Mage_Sales_Model_Order_Pdf_Abstract
{
    public function getPdf($orders = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('order');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($orders as $order) {
            if ($order->getStoreId()) {
                Mage::app()->getLocale()->emulate($order->getStoreId());
                Mage::app()->setCurrentStore($order->getStoreId());
            }
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $pdf->pages[] = $page;

            /* Add image */
            $this->insertLogo($page, $order->getStore());
			/* Add Phone Number for Hackley Health Management */
            $this->_setFontRegular($page, 18);
			$page->drawText(Mage::helper('sales')->__('800.521.9054'), 480, $this->y + 5, 'UTF-8');
            /* Add address */
            $this->insertAddress($page, $order->getStore());

            /* Add head */
            $this->insertOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId()));


            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $this->_setFontRegular($page);

/**** COMMENTS *****/
			/* Add table */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
			$this->y +=10;
            $page->drawRectangle(25, $this->y, 570, $this->y -15);
            $this->y -=10;

            /* Add table head */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
            //$page->drawText(Mage::helper('sales')->__('____'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('CUSTOMER COMMENTS:'), 35, $this->y, 'UTF-8');
			
			// Split the string using WordWrap
			$zNotes = wordwrap($order->getCustomerNote(), 180, "\n");
			// Magento PDF won't recognize newling character, so loop through the string as an array
			foreach(explode("\n", $zNotes) as $newLine) {
				// If the new line isn't empty
				if($newLine != '') {
					// Print the new line
					$page->drawText($newLine, 35, $this->y -15,'UTF-8');
					// Move to a new line
					$this->y -=15;
				}
			}
			
			
			/*
			$rows = ceil(strlen($order->getCustomerNote()) / 180);
			
			for($j=0, $k=0; $j<$rows; $j++, $k+=180){
				$myString = substr($order->getCustomerNote(),$k,180);
				$page->drawText($myString, 35, $this->y -15,'UTF-8');
				$this->y -=15;
			}
			*/
//			$page->drawText($order->getCustomerNote(), 35, $this->y -15,'UTF-8');
			//$page->drawText($rows, 35, $this->y -15,'UTF-8');
			
            $this->y -=25;

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));


/**** COMMENTS END *****/

            /* Add table */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);

            $page->drawRectangle(25, $this->y, 570, $this->y -15);
            $this->y -=10;

            /* Add table head */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
            //$page->drawText(Mage::helper('sales')->__('____'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Qty'), 85, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Product'), 135, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Price'), 380, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax'), 480, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Subtotal'), 535, $this->y, 'UTF-8');

            $this->y -=15;

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

			// Expanded to sort by category for client.
			// mbitson - 8-13-14

			// Build array to contain products by category.
			$orderItems = array();
			foreach($order->getAllItems() as $orderItem){
				$product = Mage::getModel('catalog/product')->load($orderItem->getProductId());
				$cats = $product->getCategoryIds();
				$orderItems[$cats[0]][] = $orderItem;
			}

			// Sort array.
			ksort($orderItems);

			// Run normal add.
			foreach($orderItems as $categoryOrderItemCollection)
			{
				/* Add body */
				foreach ($categoryOrderItemCollection as $orderItem)
				{
					// Create new item (Varien object for magic getter setters).
					$item = new Varien_Object();

					// Set all necessary data for new object.
					$item->setOrderItem($orderItem)
						->setQty($orderItem->getQty())
						->setName($orderItem->getName())
						->setPrice($orderItem->getPrice())
						->setTaxAmount($orderItem->getTaxAmount())
						->setRowTotal($orderItem->getRowTotal());

					// If this product has a parent, do not add it to the pdf..
					if ($item->getOrderItem()->getParentItem()) {
						continue;
					}

					// If we're out of vertical space, start a new page.
					if ($this->y < 15) {
						$page = $this->newPage(array('table_header' => true));
					}

					/* Draw item */
					$page = $this->_drawItem($item, $page, $order);
				}
			}


            /* Add totals */
            $facade = Mage::getModel('printorder/sales_order_facade');
            foreach($order->getData() as $key=>$value) {
                $facade->setData($key, $value);
            }
            $facade->setOrder($order);

            $page = $this->insertTotals($page, $facade);

            if ($order->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
        }

        $this->_afterGetPdf();

        return $pdf;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;

        if (!empty($settings['table_header'])) {
            $this->_setFontRegular($page);
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(25, $this->y, 570, $this->y-15);
            $this->y -=10;

            $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
            //$page->drawText(Mage::helper('sales')->__('____'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Qty'), 85, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Product'), 135, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Price'), 380, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax'), 480, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Subtotal'), 535, $this->y, 'UTF-8');
			/*$page->drawText(Mage::helper('sales')->__('Qty'), 430, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Product'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('SKU'), 255, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Price'), 380, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax'), 480, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Subtotal'), 535, $this->y, 'UTF-8');*/

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->y -=20;
        }

        return $page;
    }

}
