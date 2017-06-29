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

            /* Add address */
            $this->insertAddress($page, $order->getStore());

            /* Add head */
            $this->insertOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId()));


            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $this->_setFontRegular($page);

            /* Add table */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);

            $page->drawRectangle(25, $this->y, 570, $this->y -15);
            $this->y -=10;

            /* Add table head */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
            $page->drawText(Mage::helper('sales')->__('Products'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('SKU'), 255, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Price'), 380, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Qty'), 430, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax'), 480, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Subtotal'), 535, $this->y, 'UTF-8');

            $this->y -=15;

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            /* Add body */
            foreach ($order->getAllItems() as $orderItem){
                $item = new Varien_Object();
                $item->setOrderItem($orderItem)
                    ->setName($orderItem->getName())
                    ->setSku($orderItem->getSku())
                    ->setQty($orderItem->getQty())
                    ->setPrice($orderItem->getPrice())
                    ->setTaxAmount($orderItem->getTaxAmount())
                    ->setRowTotal($orderItem->getRowTotal());
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                if ($this->y < 15) {
                    $page = $this->newPage(array('table_header' => true));
                }

                /* Draw item */
                $page = $this->_drawItem($item, $page, $order);
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
            $page->drawText(Mage::helper('sales')->__('Product'), 35, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('SKU'), 255, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Price'), 380, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Qty'), 430, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Tax'), 480, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Subtotal'), 535, $this->y, 'UTF-8');

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->y -=20;
        }

        return $page;
    }

}
