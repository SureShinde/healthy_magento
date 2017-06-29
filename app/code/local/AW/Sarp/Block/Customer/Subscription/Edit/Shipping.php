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

class AW_Sarp_Block_Customer_Subscription_Edit_Shipping extends Mage_Directory_Block_Data
{

    protected $_order;


    public function _construct()
    {
        $this->setTemplate('customer/address/edit.phtml');
        return $this;
    }

    public function getTitle()
    {
        return $this->__("Subscription Details - Shipping Address");
    }


    public function getCountryId()
    {
        $countryId = $this->getAddress()->getCountryId();
        return $countryId;
    }

    /**
     * Returns billing address for order
     * @return object
     */
    public function getAddress()
    {
        return $this->getSubscription()->getOrder()->getShippingAddress();
    }

    public function isDefaultBilling()
    {
        return $this->getAddress()->getId() && $this->getAddress()->getId() == Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
    }

    public function isDefaultShipping()
    {
        return $this->getAddress()->getId() && $this->getAddress()->getId() == Mage::getSingleton('customer/session')->getCustomer()->getDefaultShipping();
    }

    public function canSetAsDefaultBilling()
    {
        return false;
    }

    public function canSetAsDefaultShipping()
    {
        return false;
    }

    public function getSaveUrl()
    {
        return Mage::getUrl('sarp/customer/save', array('section' => 'shipping', 'id' => $this->getRequest()->getParam('id')));
    }

    /**
     * Returns back url
     * @return string
     */
    public function getBackUrl()
    {
        return Mage::getUrl('*/*/view', array('id' => $this->getRequest()->getParam('id')));
    }
}