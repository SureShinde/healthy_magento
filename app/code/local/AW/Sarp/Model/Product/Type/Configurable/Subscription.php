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

class AW_Sarp_Model_Product_Type_Configurable_Subscription extends Mage_Catalog_Model_Product_Type_Configurable
{
    protected $_canConfigure = true;

    const PRODUCT_TYPE_CONFIGURABLE = 'subscription_configurable';

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and then add Configurable specific options.
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        if (!$product->getAwSarpEnabled()) {
            return;
        }

        Mage::getModel('sarp/product_type_default')->checkPeriod($product, $buyRequest);
        $Period = Mage::getModel('sarp/period');

        /* We should add custom options that doesn't exist */
        if ($buyRequest->getAwSarpSubscriptionType()) {
            if ($Period->load($buyRequest->getAwSarpSubscriptionType())->getId()) {
                $product->addCustomOption('aw_sarp_subscription_type', $Period->getId());
            }
        }

        if (
            (empty($options['aw_sarp_subscription_start']['month']) || empty($options['aw_sarp_subscription_start']['day']) || empty($options['aw_sarp_subscription_start']['year']))
            && $buyRequest->getAwSarpSubscriptionType() != AW_Sarp_Model_Period::PERIOD_TYPE_NONE
        ) {
            $date = new Zend_Date;
            $buyRequest->setAwSarpSubscriptionStart($date->toString(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)));
        }

        if (
            isset($options['aw_sarp_subscription_start']) && is_array($options['aw_sarp_subscription_start']) && $buyRequest->getAwSarpSubscriptionType() != AW_Sarp_Model_Period::PERIOD_TYPE_NONE
            && !empty($options['aw_sarp_subscription_start']['month']) && !empty($options['aw_sarp_subscription_start']['day']) && !empty($options['aw_sarp_subscription_start']['year'])
        ) {
            $subscriptionStart = $options['aw_sarp_subscription_start'];
            $date = new Zend_Date();
            $date
                ->setMinute(0)
                ->setHour(0)
                ->setSecond(0)
                ->setDay($subscriptionStart['day'])
                ->setMonth($subscriptionStart['month'])
                ->setYear($subscriptionStart['year']);
            $buyRequest->setAwSarpSubscriptionStart($date->toString(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)));
        }

        if (!is_null($buyRequest->getAwSarpSubscriptionStart()) && $Period->getId()) {
            $start = $buyRequest->getAwSarpSubscriptionStart();

            if (!empty($start)) {
                $date = new Zend_Date($start, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
            } else {
                $date = $Period->getNearestAvailableDay();
            }

            $performDateCompare = !AW_Sarp_Model_Cron::$isCronSession;

            $today = new Zend_Date;
            if (!$this->isVirtual($product)) {
                $today->addDayOfYear($Period->getPaymentOffset());
            }

            if (
                $performDateCompare &&
                ($date->compare($today, Zend_Date::DATE_SHORT) < 0 || !$Period->isAllowedDate($date, $product))
            ) {
                $date = $Period->getNearestAvailableDay();
            }
        } else {
            $date = Mage::app()->getLocale()->date();
        }

        $product->addCustomOption('aw_sarp_subscription_start', $date->toString('Y-MM-dd'));

        $_result = parent::_prepareProduct($buyRequest, $product, $processMode);
        if (is_array($_result)) {
            if ($buyRequest->getAwSarpSubscriptionType()) {
                if ($Period->getId()) {
                    $_result[0]->addCustomOption('aw_sarp_subscription_start', $date->toString('Y-MM-dd'));
                    $_result[0]->addCustomOption('aw_sarp_subscription_type', $Period->getId());
                }
            }
            return $_result;
        }
        return $this->getSpecifyOptionMessage();
    }

    public function getProductByAttributes($attributesInfo, $product = null)
    {
        $_result = parent::getProductByAttributes($attributesInfo, $product);
        if (is_object($_result)) {
            $_result = Mage::getModel('catalog/product')->load($_result->getId());
        }
        return $_result;
    }

    /**
     * @param $product
     * @return bool
     */
    public function hasRequiredOptions($product = null)
    {
        return !!$product->getAwSarpEnabled();
    }

    /**
     * Returns true if product has subscriptions options
     * @return bool
     */
    public function hasSubscriptionOptions()
    {
        $opts = @preg_split('/[,]/', $this->getProduct()->getAwSarpPeriod(), -1, PREG_SPLIT_NO_EMPTY);
        if (!sizeof($opts) || ($opts[0] == -1 && sizeof($opts) == 1)) {
            return false;
        }
        return true;
    }

    /**
     * Returns true if product requires subscription options
     * @return bool
     */
    public function requiresSubscriptionOptions($product = null)
    {
        if (is_null($product)) $product = $this->getProduct();
        if (!$product->getAwSarpEnabled()) return false;
        $opts = @preg_split('/[,]/', $product->getAwSarpPeriod(), -1, PREG_SPLIT_NO_EMPTY);
        if (!sizeof($opts) || (array_search(-1, $opts) !== false)) {
            return false;
        }
        return true;
    }

    /**
     * Returns default period id. If none, returns -1
     * @return int
     */
    public function getDefaultSubscriptionPeriodId()
    {
        $opts = @preg_split('/[,]/', $this->getProduct()->getAwSarpPeriod(), -1, PREG_SPLIT_NO_EMPTY);
        return isset($opts[0]) ? $opts[0] : AW_Sarp_Model_Period::PERIOD_TYPE_NONE;
    }

    public function processBuyRequest($product, $buyRequest)
    {
        $toReturn = parent::processBuyRequest($product, $buyRequest);
        if ($buyRequest->getData('aw_sarp_subscription_start')) $toReturn['aw_sarp_subscription_start'] = $buyRequest->getData('aw_sarp_subscription_start');
        if ($buyRequest->getData('aw_sarp_subscription_type')) $toReturn['aw_sarp_subscription_type'] = $buyRequest->getData('aw_sarp_subscription_type');
        return $toReturn;
    }

    public function beforeSave($product = null)
    {
        parent::beforeSave($product);
        if ($product->getAwSarpEnabled() && $this->getProduct($product)->getAwSarpSubscriptionPrice() == '')
            $this->getProduct($product)->setAwSarpSubscriptionPrice($product->getData('price'));
    }

}