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


class AW_Sarp_Model_Payment_Method_Core_Eway_Direct extends Mage_Eway_Model_Direct
{

    public function validate()
    {
        if (AW_Sarp_Model_Subscription::isIterating()) {
            return $this;
        } else {
            $res = parent::validate();
            /*check is sarp product has*/
            if ($this->__isQuoteHasSarpItems()) {
                //this will be code of validation before placing order on onepage checkout
                $payment = $this->getInfoInstance();
                //validate on country (au or uk)
                $countryId = strtolower($payment->getQuote()->getBillingAddress()->getCountryId());
                if (!in_array($countryId, array('au','gb'))) {
                    Mage::throwException(Mage::helper('payment')->__('Selected payment type is not allowed for billing country.'));
                }
            }
            return $res;
        }
    }

    public function capture(Varien_Object $payment, $amount)
    {
        if (AW_Sarp_Model_Subscription::isIterating()) {
            $Subscription = AW_Sarp_Model_Subscription::getInstance()->processPayment($payment->getOrder());
            return $this;
        }
        return parent::capture($payment, $amount);
    }

    private function __isQuoteHasSarpItems()
    {

        if (is_null($this->getInfoInstance()->getQuote())) {
            return false;
        }
        $haveSarpItems = false;
        foreach ($this->getInfoInstance()->getQuote()->getAllItems() as $item)
        {
            $sarpSubscriptionType = $item->getProduct()->getCustomOption('aw_sarp_subscription_type');
            if (Mage::helper('sarp')->isSubscriptionType($item) && !is_null($sarpSubscriptionType)) {
                $haveSarpItems = true;
                break;
            }
        }
        return $haveSarpItems;
    }
}