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

/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Enterprise
 * @package     Enterprise_GiftCardAccount
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

class AW_Sarp_Model_Enterprise_GiftCardAccount_GiftCardAccount extends Enterprise_GiftCardAccount_Model_Giftcardaccount
{
    /**
     * Add gift card to quote gift card storage
     *
     * @param bool $saveQuote
     * @return Enterprise_GiftCardAccount_Model_Giftcardaccount
     */
    public function addToCart($saveQuote = true, $quote = null)
    {
        if (is_null($quote)) {
            $quote = $this->_getCheckoutSession()->getQuote();
        }

        foreach ($quote->getAllItems() as $item)
            if (Mage::helper('sarp')->isSubscriptionType($item))
                Mage::throwException(Mage::helper('enterprise_giftcardaccount')->__('Subscription products can\'t be used with gift cards'));

        return parent::addToCart($saveQuote, $quote);
    }
}
