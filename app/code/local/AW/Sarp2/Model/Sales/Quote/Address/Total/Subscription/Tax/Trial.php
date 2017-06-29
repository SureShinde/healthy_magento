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


class AW_Sarp2_Model_Sales_Quote_Address_Total_Subscription_Tax_Trial
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    protected $_itemRowTotalKey = 'subscription_trial_tax';

    /**
     * Don't add amounts to address
     *
     * @var bool
     */
    protected $_canAddAmountToAddress = false;

    /**
     * Don't fetch anything
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return array
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        $store = $address->getQuote()->getStore();
        $shippingIncludesTax = Mage::getSingleton('tax/config')->shippingPriceIncludesTax($store);
        $items = $this->_getAddressItems($address);

        foreach ($items as $item) {
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
                if (Mage::helper('aw_sarp2/config')->isTaxOnTrialEnabled($store)) {
                    if ($item->getRowTotal() != 0) {
                        $rate = $item->getData('subscription_trial_payment') / $item->getRowTotal();
                        if (Mage::helper('aw_sarp2/config')->isTaxOnShippingEnabled($store)) {
                            if ($shippingIncludesTax) {
                                $trialTax = round($item->getBaseTaxAmount() * $rate, 2);
                            } else {
                                $trialTax = round(($item->getBaseTaxAmount() - $address->getBaseShippingTaxAmount()) * $rate
                                    + $address->getBaseShippingTaxAmount(), 2);
                            }
                        } else {
                            $trialTax = round($item->getBaseTaxAmount() * $rate, 2);
                        }
                    }
                } else {
                    if (Mage::helper('aw_sarp2/config')->isTaxOnShippingEnabled($store)) {
                        if ($shippingIncludesTax) {
                            $trialTax = 0;
                        } else {
                            if ($address->getBaseShippingTaxAmount()) {
                                $trialTax = round($address->getBaseShippingTaxAmount(), 2);
                            } else {
                                $trialTax = 0;
                            }
                        }
                    } else {
                        $trialTax = 0;
                    }
                }
                $item->setData(
                    'base_' . $this->_itemRowTotalKey,
                    $trialTax
                );
                $item->setData(
                    $this->_itemRowTotalKey,
                    (float)$address->getQuote()->getStore()->convertPrice($trialTax)
                );
            }
        }
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        return Mage_Sales_Model_Quote_Address_Total_Abstract::fetch($address);
    }

    /**
     * Get subscription items only
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return array
     */
    protected function _getAddressItems(Mage_Sales_Model_Quote_Address $address)
    {
        return Mage::helper('aw_sarp2/quote')->getAllSubscriptionItemsFromQuote($address->getQuote());
    }

    public function getLabel()
    {
        return Mage::helper('aw_sarp2')->__('Trial Tax');
    }
}