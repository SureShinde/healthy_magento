<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Order Invoice PDF model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Pdf_Invoice extends Mage_Sales_Model_Order_Pdf_Abstract
{
    /**
     * Draw header for item table
     *
     * @param Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y -15);
        $this->y -= 10;
        $page->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0));
        //columns headers
        $lines[0][] = array(
            'text'  => 'Checked',
            'feed'  => 35,
            'align' => 'left'
        );
        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Qty'),
            'feed'  => 120,
            'align' => 'right'
        );
        $lines[0][] = array(
            'text' => Mage::helper('sales')->__('Category – Product Name'),
            'feed' => 150
        );
        /*$lines[0][] = array(
            'text'  => Mage::helper('sales')->__('SKU'),
            'feed'  => 290,
            'align' => 'right'
        );*/
        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Price'),
            'feed'  => 415,
            'align' => 'right'
        );
        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Tax'),
            'feed'  => 495,
            'align' => 'right'
        );
        $lines[0][] = array(
            'text'  => Mage::helper('sales')->__('Subtotal'),
            'feed'  => 565,
            'align' => 'right'
        );
        $lineBlock = array(
            'lines'  => $lines,
            'height' => 5
        );
        $this->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    public function getComments($orderid){
        $coreResource = Mage::getSingleton('core/resource');
        $connection = $coreResource->getConnection('core_read');
        $sql = "SELECT * FROM kv_mailings WHERE sku=?";
        $row = $connection->fetchRow($sql, 'hkag' );
    }

    /**
     * Return PDF document
     *
     * @param  array $invoices
     * @return Zend_Pdf
     */
    public function getPdf($invoices = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
                Mage::app()->setCurrentStore($invoice->getStoreId());
            }
            $page  = $this->newPage();
            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId())
            );
            /* Add document text and number */
            $this->insertDocumentNumber(
                $page,
                Mage::helper('sales')->__('Invoice # ') . $invoice->getIncrementId()
            );

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
            $this->y -=25;
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            /**** COMMENTS END *****/

            /* Add table */
            $this->_drawHeader($page);
            /* Add body */

			/**********
			 ORIGINAL BLOCK
			**********/
			 foreach ($invoice->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue; 
			/**********
			 MODIFIED BLOCK - My attempt...this will sort titles by alpha using ksort, but it still needs grouped by category....
			**********/
			/* $items = $invoice->getAllItems()  ; // Gets items --- need to sort them first! 
            $_sortedItems = array();                        // build array, inserts order items into array and sort
            foreach ($items as $item) :
                $_sortedItems[$item->getName()] = $item;
            endforeach;

            ksort($_sortedItems); // sorting alpha, but still need a way to group...by category
            foreach ($_sortedItems as $item) {   //pass sorted items back one at a time in alpha' order

            if ($item->getOrderItem()->getParentItem()) {
                continue;
			*/  }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            /* Add totals */
            $this->insertTotals($page, $invoice);
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param  array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_A4);
        $this->_getPdf()->pages[] = $page;
        $this->y = 800;
        if (!empty($settings['table_header'])) {
            $this->_drawHeader($page);
        }
        return $page;
    }
}
