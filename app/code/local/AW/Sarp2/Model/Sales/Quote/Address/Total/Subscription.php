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


class AW_Sarp2_Model_Sales_Quote_Address_Total_Subscription extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    protected $_totalsOrder = array(
        'AW_Sarp2_Model_Sales_Quote_Address_Total_Subscription_Initial'     => 10,
        'AW_Sarp2_Model_Sales_Quote_Address_Total_Subscription_Trial'       => 20,
        'AW_Sarp2_Model_Sales_Quote_Address_Total_Subscription_Tax_Trial'   => 40,
        'AW_Sarp2_Model_Sales_Quote_Address_Total_Subscription_Subtotal'    => 50,
        'AW_Sarp2_Model_Sales_Quote_Address_Total_Subscription_Shipping'    => 60,
        'AW_Sarp2_Model_Sales_Quote_Address_Total_Subscription_Tax'         => 70,
    );

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        //++HACK for remove subscription items from nonNominal items
        $newNonNominalItems = array();
        $nonNominalItems = $address->getAllNonNominalItems();
        foreach ($nonNominalItems as $item) {
            if (!Mage::helper('aw_sarp2/quote')->isQuoteItemIsSubscriptionProduct($item)) {
                $newNonNominalItems[] = $item;
            }
        }
        $address->setData('cached_items_nonnominal', $newNonNominalItems);
        //--HACK for remove subscription items from nonominal items
        $items = Mage::helper('aw_sarp2/quote')->getAllSubscriptionItemsFromQuote($address->getQuote());
        $this->_setItemsTotals($items, $address);
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $items = Mage::helper('aw_sarp2/quote')->getAllSubscriptionItemsFromQuote($address->getQuote());
        if ($items) {
            $this->_setItemsTotals($items, $address);
            $address->addTotal(
                array(
                     'code'  => $this->getCode(),
                     'title' => Mage::helper('aw_sarp2')->__('Subscription Items'),
                     'items' => $items,
                     'area'  => 'footer',
                )
            );
        }
        return $this;
    }

    protected function _setItemsTotals($items, $address)
    {
        if ($address->getSarpItemsTotalsCollectedFlag()) {
            return $this;
        }
        $collector = Mage::getSingleton(
            'aw_sarp2/sales_quote_address_total_subscription_collector',
            array('store' => $address->getQuote()->getStore())
        );
        foreach ($collector->getCollectors() as $model) {
            $model->collect($address);
        }
        foreach ($items as $item) {
            $rowTotal = 0;
            $baseRowTotal = 0;
            $totalDetails = array();
            foreach ($collector->getCollectors() as $model) {
                $itemRowTotal = $model->getItemRowTotal($item);
                if ($model->getIsItemRowTotalCompoundable($item)) {
                    $rowTotal += $itemRowTotal;
                    $baseRowTotal += $model->getItemBaseRowTotal($item);
                    $isCompounded = true;
                } else {
                    $isCompounded = false;
                }

                if ((/*is_float($itemRowTotal) ||*/ $itemRowTotal > 0) && $label = $model->getLabel()) {
                    $totalDetails[] = new Varien_Object(
                        array(
                            'name'  => $this->_getTotalName($model),
                            'label' => $label,
                            'amount' => $itemRowTotal,
                            'is_compounded' => $isCompounded,
                            'order' => $this->_getOrderForTotal($model),
                        )
                    );
                }
            }
            if (Mage::helper('aw_sarp2/quote')->isQuoteItemSubscriptionHasTrialEnabled($item)) {
                foreach ($totalDetails as $total) {
                    if ($total['name'] == 'subscription_shipping') {
                        $trialShipping = clone $total;
                        $trialShipping['order'] = 30;
                        $trialShipping['label'] = Mage::helper('aw_sarp2')->__('Trial Shipping');
                        $totalDetails[] = $trialShipping;
                    }
                }
                usort($totalDetails, array($this, '_sortTotals'));
            }
            $item->setSubscriptionRowTotal($rowTotal);
            $item->setBaseSubscriptionRowTotal($baseRowTotal);
            $item->setSubscriptionTotalDetails($totalDetails);
        }
        $address->setSarpItemsTotalsCollectedFlag(true);
        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address_Total_Abstract $model
     * @return int
     */
    protected function _getOrderForTotal(Mage_Sales_Model_Quote_Address_Total_Abstract $model)
    {
        $class = get_class($model);
        $order = $this->_totalsOrder[$class];
        return $order;
    }

    protected function _sortTotals($a, $b)
    {
        if ($a['order'] == $b['order']) {
            return 0;
        }
        return ($a['order'] > $b['order']) ? +1 : -1;
    }

    protected function _getTotalName(Mage_Sales_Model_Quote_Address_Total_Abstract $model)
    {
        $name = str_replace('AW_Sarp2_Model_Sales_Quote_Address_Total_', '', get_class($model));
        return strtolower($name);
    }
}