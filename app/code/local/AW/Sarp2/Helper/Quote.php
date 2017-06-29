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


class AW_Sarp2_Helper_Quote
{
    public function getAllSubscriptionItemsFromQuote(Mage_Sales_Model_Quote $quote)
    {
        $subscriptionItems = array();
        if (is_null($quote)) {
            return $subscriptionItems;
        }
        foreach ($quote->getItemsCollection() as $item) {
            if ($this->isQuoteItemIsSubscriptionProduct($item)) {
                $subscriptionItems[] = $item;
            }
        }
        return $subscriptionItems;
    }

    public function isQuoteHasSubscriptionProduct(Mage_Sales_Model_Quote $quote)
    {
        $subscriptionItems = $this->getAllSubscriptionItemsFromQuote($quote);
        return (count($subscriptionItems) > 0);
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $item
     *
     * @return string|null
     */
    public function getSubscriptionTypeOptionFromQuoteItem(Mage_Sales_Model_Quote_Item $item)
    {
        $buyRequest = null;
        if (!is_null($item->getOptionByCode('info_buyRequest'))) {
            $buyRequest = @unserialize($item->getOptionByCode('info_buyRequest')->getValue());
        }
        return $this->getSubscriptionTypeOptionFromBuyRequest($buyRequest);
    }

    /**
     * @param $buyRequest
     *
     * @return string|null
     */
    public function getSubscriptionTypeOptionFromBuyRequest($buyRequest)
    {
        if (!is_null($buyRequest)){
            return @$buyRequest['options'][AW_Sarp2_Helper_Subscription::SUBSCRIPTION_TYPE_SELECTOR_PRODUCT_OPTION_ID];
        }
        return null;

    }

    /**
     * @param Mage_Sales_Model_Quote_Item $item
     *
     * @return Zend_Date
     */
    public function getSubscriptionStartDateOptionFromQuoteItem(Mage_Sales_Model_Quote_Item $item)
    {
        $buyRequest = @unserialize($item->getOptionByCode('info_buyRequest')->getValue());
        $date = $this->getSubscriptionStartDateOptionFromBuyRequest($buyRequest);
        $format = Mage::helper('aw_sarp2')->getDateFormatWithLongYear(Mage::app()->getLocale());
        /** @var Zend_Date $dateAsZendDate */
        $dateAsZendDate = Mage::app()->getLocale()->storeDate(null, null, false);
        if (!is_null($date)) {
            $dateAsZendDate->setDate($date, $format);
        }
        return $dateAsZendDate;
    }

    /**
     * @param $buyRequest
     *
     * @return string|null
     */
    public function getSubscriptionStartDateOptionFromBuyRequest($buyRequest)
    {
        $optionId = AW_Sarp2_Helper_Subscription::SUBSCRIPTION_START_DATE_PRODUCT_OPTION_ID;
        if (!array_key_exists($optionId, is_array(@$buyRequest['options']) ? @$buyRequest['options'] : array())) {
            return null;
        }
        $date = Mage::app()->getLocale()->storeDate(null, null, true);
        if (is_array($buyRequest['options'][$optionId]) && array_key_exists('date', $buyRequest['options'][$optionId])) {
            $dateAsString = @$buyRequest['options'][$optionId]['date'];
            $inputFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            $date = Mage::app()->getLocale()->date($dateAsString, $inputFormat, null, false);
        } else {
            $dateParts = @$buyRequest['options'][$optionId];
            if (!is_null($dateParts) && is_array($dateParts) && count(array_filter($dateParts)) == 3) {
                $date
                    ->setYear($dateParts['year'])
                    ->setMonth($dateParts['month'])
                    ->setDay($dateParts['day'])
                ;
            }
        }
        $format = Mage::helper('aw_sarp2')->getDateFormatWithLongYear(Mage::app()->getLocale());
        return $date->toString($format);
    }

    /**
     * @param Mage_Sales_Model_Quote_Item $item
     *
     * @return bool
     */
    public function isQuoteItemIsSubscriptionProduct(Mage_Sales_Model_Quote_Item $item)
    {
        $subscription = Mage::getModel('aw_sarp2/subscription')->loadByProductId($item->getProduct()->getId());

        $subscriptionTypeOption = Mage::helper('aw_sarp2/quote')->getSubscriptionTypeOptionFromQuoteItem($item);
        $isSubscriptionProduct = !is_null($subscription->getId())
            && $subscriptionTypeOption
            && $subscriptionTypeOption
            !== AW_Sarp2_Helper_Subscription::SUBSCRIPTION_TYPE_SELECTOR_NO_SUBSCRIPTION_OPTION_VALUE
        ;
        return $isSubscriptionProduct;
    }

    public function isQuoteItemSubscriptionHasTrialEnabled(Mage_Sales_Model_Quote_Item $item)
    {
        $subscriptionTypeOption = Mage::helper('aw_sarp2/quote')->getSubscriptionTypeOptionFromQuoteItem($item);
        $subscriptionItem = Mage::getModel('aw_sarp2/subscription_item')->load($subscriptionTypeOption);
        if (
            Mage::helper('aw_sarp2')->isEnabled()
            && $subscriptionItem->getTypeModel()
            && $subscriptionItem->getTypeModel()->getTrialIsEnabled()
            && $subscriptionItem->getTypeModel()->getEngineModel()
            && $subscriptionItem->getTypeModel()->getEngineModel()->getPaymentRestrictionsModel()
                ->isTrialSupported()
        ) {
            return true;
        }
        return false;
    }
}