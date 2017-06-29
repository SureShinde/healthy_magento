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

class AW_Sarp_Model_Order_Section_Shipping extends AW_Sarp_Model_Order_Section
{

    /**
     * Saves billing section to order
     * @param Mage_Sales_Model_Order $Order
     * @return
     */
    public function preset(AW_Sarp_Model_Subscription $subscription, $data)
    {
        $order = $subscription->getOrder();
        $shippingAddress = $order->getShippingAddress();
        foreach ($data as $key => $value) {
            $shippingAddress->setData($key, $value);
        }
        $shippingAddress->implodeStreetAddress();
        $shippingAddress->save();

        return $order;
    }
}