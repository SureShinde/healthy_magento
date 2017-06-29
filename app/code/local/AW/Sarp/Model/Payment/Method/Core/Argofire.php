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

class AW_Sarp_Model_Payment_Method_Core_Argofire extends Acreativellc_Argofire_Model_Argofire
{
    /**
     * Validate payment method information object
     *
     * @param   Mage_Payment_Model_Info $info
     * @return  Mage_Payment_Model_Abstract
     */
    public function validate()
    {

        if (AW_Sarp_Model_Subscription::isIterating()) {
            return $this;
        } else {
            return parent::validate();
        }
    }

    public function capture(Varien_Object $payment, $amount)
    {
        if (AW_Sarp_Model_Subscription::isIterating()) {
            $Subscription = AW_Sarp_Model_Subscription::getInstance()->processPayment($payment->getOrder());
            return $this;
        }

        if (Mage::getModel('sarp/sequence')->load($payment->getOrder()->getId(), 'order_id')->getId()) {
            if (Mage::getModel('sarp/sequence')->load($payment->getOrder()->getId(), 'order_id')->getStatus() != AW_Sarp_Model_Sequence::STATUS_FAILED) {
                return $this;
            }
        }

        return parent::capture($payment, $amount);
    }

    public function authorize(Varien_Object $payment, $amount)
    {
        if (AW_Sarp_Model_Subscription::isIterating()) {
            $Subscription = AW_Sarp_Model_Subscription::getInstance()->processPayment($payment->getOrder());
            return $this;
        }
        return parent::authorize($payment, $amount);
    }
}