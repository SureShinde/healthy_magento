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

if (!@class_exists('AW_Sarp_Block_Checkout_Onepage_Payment_Methods_Parent')) {
    if (@class_exists('AW_Points_Block_Checkout_Onepage_Payment_Methods')
        && Mage::getConfig()->getModuleConfig('AW_Points')->is('active')
        && Mage::helper('points/config')->isPointsEnabled()
    ) {
        class AW_Sarp_Block_Checkout_Onepage_Payment_Methods_Parent extends AW_Points_Block_Checkout_Onepage_Payment_Methods {}
    }
    else {
        class AW_Sarp_Block_Checkout_Onepage_Payment_Methods_Parent extends Mage_Checkout_Block_Onepage_Payment_Methods {}
    }
}
class AW_Sarp_Block_Checkout_Onepage_Payment_Methods extends AW_Sarp_Block_Checkout_Onepage_Payment_Methods_Parent
{
    public function getMethods()
    {
        $methods = $this->getData('methods');
        if (is_null($methods)) {
            $quote = $this->getQuote();
            $store = $quote ? $quote->getStoreId() : null;
            $methods = $this->helper('payment')->getStoreMethods($store, $quote);
            $total = $quote->getGrandTotal();
            foreach ($methods as $key => $method) {
                if ($this->_canUseMethod($method)
                    && ($total != 0
                        || $method->getCode() == 'free'
                        || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles()))) {
                    $this->_assignMethod($method);
                } else {
                    unset($methods[$key]);
                }
            }
            $this->setData('methods', $methods);
        }
        return $methods;
    }
}