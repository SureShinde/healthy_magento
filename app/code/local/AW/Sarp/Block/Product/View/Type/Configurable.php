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

class AW_Sarp_Block_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable
{
    protected $_subscriptionInstance;

    public function __construct()
    {
        $this->_subscriptionInstance = new AW_Sarp_Block_Product_View_Type_Subscription($this);
        return parent::__construct();
    }

    public function getSubscription()
    {
        return $this->_subscriptionInstance;
    }

    /**
     * Return current template
     * @return string
     */
    public function getTemplate()
    {
        if (parent::getTemplate()) return parent::getTemplate();
        return $this->getSubscription()->getTemplate();
    }

    public function getPredefinedStartDate()
    {
        $_editableOptions = $this->getProduct()->getPreconfiguredValues();
        if (isset($_editableOptions['aw_sarp_subscription_start'])) {
            $date = $_editableOptions['aw_sarp_subscription_start'];
            $zDate = new Zend_Date();
            $zDate->setDate($date);
            $today = new Zend_Date();
            $period = Mage::getModel('sarp/period')->load($_editableOptions['aw_sarp_subscription_type']);
            if (($zDate->compare($today, Zend_Date::DATE_SHORT) < 0) || !$period->isAllowedDate($zDate, $this->getProduct())) {
                $zDate = $period->getNearestAvailableDay();
            }
            $date = $zDate->toString(preg_replace(array('/M+/', '/d+/'), array('MM', 'dd'), Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)));
            return $date;
        }
        return null;
    }
}