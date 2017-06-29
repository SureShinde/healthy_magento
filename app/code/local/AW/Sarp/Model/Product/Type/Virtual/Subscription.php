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

class AW_Sarp_Model_Product_Type_Virtual_Subscription extends Mage_Catalog_Model_Product_Type_Abstract
{

    protected $_canConfigure = true;

    /**
     * Prepares product for cart according to buyRequest.
     *
     * @param Varien_Object $buyRequest
     * @param object        $product [optional]
     * @return
     */
    public function prepareForCart(Varien_Object $buyRequest, $product = null, $old = false)
    {
        if (!$product->getAwSarpEnabled()) {
            if (!$old) {
                return parent::prepareForCart($buyRequest, $product);
            }
            return;
        }

        Mage::getModel('sarp/product_type_default')->checkPeriod($product, $buyRequest);

        /*
         * For creating order from admin
         * If product is added to cart from admin, we doesn't add sart custom options to it.
         */
        $Period = Mage::getModel('sarp/period');

        if ($product->getAwSarpPeriod()) {
            if (count(explode(",", $product->getAwSarpPeriod())) === 1) {
                $date = Mage::getModel('sarp/period')->load($product->getAwSarpPeriod())->getNearestAvailableDay();
                $product->setAwSarpSubscriptionType($product->getAwSarpPeriod());
                $product->setAwSarpSubscriptionStart($date->toString(), Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
            }
        }

        /* We should add custom options that doesnt exist */
        if ($buyRequest->getAwSarpSubscriptionType()) {
            if ($Period->load($buyRequest->getAwSarpSubscriptionType())->getId()) {
                $product->addCustomOption('aw_sarp_subscription_type', $Period->getId());
            }
        }
        else
        {
            if ($product->getAwSarpSubscriptionType()) {
                $buyRequest->setAwSarpSubscriptionType($product->getAwSarpSubscriptionType());
                $product->addCustomOption('aw_sarp_subscription_type', $product->getAwSarpSubscriptionType());
                $Period->setId($product->getAwSarpSubscriptionStart());
            }
        }

        if ($this->requiresSubscriptionOptions($product) && !$Period->getId()) {
            $date = Mage::app()->getLocale()->date();
        }

        $options = $buyRequest->getOptions();
        if (isset($options['aw_sarp_subscription_start']) && is_array($options['aw_sarp_subscription_start']) &&
        !empty($options['aw_sarp_subscription_start']['day']) && !empty($options['aw_sarp_subscription_start']['month']) &&
        !empty($options['aw_sarp_subscription_start']['year'])
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
            }
            else {
                $date = $Period->getNearestAvailableDay();
            }

            // Check date

            // Never check if start date

            //$performDateCompare = !!Mage::getSingleton('customer/session')->getCustomer()->getId();
            $performDateCompare = !AW_Sarp_Model_Cron::$isCronSession;

            $today = new Zend_Date;
            if (!$this->isVirtual($product)) {
                $today->addDayOfYear($Period->getPaymentOffset());
            }

            if ($performDateCompare
                && ($date->compare($today, Zend_Date::DATE_SHORT) < 0
                    || !$Period->isAllowedDate($date, $product))
            ) {
                $date = $Period->getNearestAvailableDay();
            }
        }
        else
        {
            $date = Mage::app()->getLocale()->date();
        }
        $product->addCustomOption('aw_sarp_subscription_start', $date->toString('Y-MM-dd'));

        if (!$old) {
            return parent::prepareForCart($buyRequest, $product);
        }
    }

    public function prepareForCartAdvanced(Varien_Object $buyRequest, $product = null, $processMode = null)
    {
        if (!$product->getAwSarpEnabled()) {
            return parent::prepareForCartAdvanced($buyRequest, $product, $processMode);
        }
        Mage::getModel('sarp/product_type_default')->checkPeriod($product, $buyRequest);
        $this->prepareForCart($buyRequest, $product, true);
        return parent::prepareForCartAdvanced($buyRequest, $product);
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

    /**
     * Returns if product is "virtual", e.g. requires no shipping
     *
     * @param object $product [optional]
     * @return bool
     */
    public function isVirtual($product = null)
    {
        return true;
    }

    public function processBuyRequest($product, $buyRequest)
    {
        $toReturn = array();
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