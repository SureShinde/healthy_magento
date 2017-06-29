<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Sarp
 * @version    1.9.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Sarp_SubscribersController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_initAction()
                ->_addContent($this->getLayout()->createBlock('sarp/adminhtml_subscribers'))
                ->renderLayout();
    }

    protected function _initAction()
    {
        $this->_title($this->__('Subscribers'));
        $this->loadLayout()
                ->_setActiveMenu('customer');
        return $this;
    }

    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName = 'subscribers.csv';
        $content = $this->getLayout()->createBlock('sarp/adminhtml_subscribers_grid')
                ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export customer grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName = 'subscribers.xml';
        $content = $this->getLayout()->createBlock('sarp/adminhtml_subscribers_grid')
                ->getXml();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _title($text = null, $resetIfExists = true)
    {
        if (!Mage::helper('sarp')->checkVersion('1.4.0.0')) {
            return $this;
        }
        return parent::_title($text, $resetIfExists);
    }
}