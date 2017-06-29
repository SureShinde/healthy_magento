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


class AW_Sarp2_Model_SalesRule_Quote_Freeshipping extends Mage_SalesRule_Model_Quote_Freeshipping
{
    /**
     * Get all items except nominals
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return array
     */
    protected function _getAddressItems(Mage_Sales_Model_Quote_Address $address)
    {
        $allItems = $address->getAllItems();
        $_allItems = array();
        $_allNominalItems = array();
        foreach ($allItems as $item) {
            $_allItems[] = $item->getId();
        }
        foreach ($address->getAllNominalItems() as $item) {
            $_allNominalItems[] = $item->getId();
        }
        $_addressItems = array_diff($_allItems, $_allNominalItems);
        $addressItems = array();
        foreach ($allItems as $item) {
            if (in_array($item->getId(), $_addressItems)) {
                $addressItems[] = $item;
            }
        }
        return $addressItems;
    }
}
