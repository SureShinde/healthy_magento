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
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Sarp2
 * @version    2.2.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Sarp2_Block_Catalog_Product_View_Options_Type_Calendar extends Mage_Core_Block_Template
{
    protected $subscriptionTypeSelectorType = null;

    /**
     * Disable previous dates in SARP calendar on product page
     *
     * @return bool
     */
    public function isDisablePreviousDates()
    {
        return true;
    }

    /**
     * Can show time in calendar
     *
     * @return bool
     */
    public function isShowsTime()
    {
        return false;
    }

    public function getProduct()
    {
        $product = Mage::registry('current_product');
        if (is_null($product) || is_null($product->getId())) {
            return null;
        }
        return $product;
    }

    public function getSubscriptionTypeOption()
    {
        $product = $this->getProduct();
        if ($product->getHasOptions()) {
            return $product->getOptionById(AW_Sarp2_Helper_Subscription::SUBSCRIPTION_TYPE_SELECTOR_PRODUCT_OPTION_ID);
        }
        return null;
    }

    public function getSubscriptionTypeSelectorType()
    {
        if (is_null($this->subscriptionTypeSelectorType)) {
            $subscriptionTypeOption = $this->getSubscriptionTypeOption();
            $this->subscriptionTypeSelectorType = $subscriptionTypeOption->getType();
        }
        return $this->subscriptionTypeSelectorType;
    }

    protected function _canShow()
    {
        if (!Mage::helper('aw_sarp2')->isEnabled()) {
            return false;
        }
        if (is_null($this->getProduct()) || is_null($this->getSubscriptionTypeOption())) {
            return false;
        }
        return true;
    }

    protected function _toHtml()
    {
        if (!$this->_canShow()) {
            return '';
        }
        return parent::_toHtml();
    }
}