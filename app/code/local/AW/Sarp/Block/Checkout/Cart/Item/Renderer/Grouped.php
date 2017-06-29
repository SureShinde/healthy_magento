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
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart item render block
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class AW_Sarp_Block_Checkout_Cart_Item_Renderer_Grouped extends Mage_Checkout_Block_Cart_Item_Renderer_Grouped
{
    protected function _getSarpOptions()
    {
        $product = $this->getProduct();
        $startDateLabel = $product->getAwSarpHasShipping() ? $this->__("First delivery:")
                : $this->__("Subscription from:");

        $subscription_options = array();
        if ($product->getCustomOption('aw_sarp_subscription_type') && $product->getCustomOption('aw_sarp_subscription_type')->getValue() > 0) {
            $subscription_options[] = array(
                'label' => $this->__('Subscription type:'),
                'value' => Mage::getModel('sarp/period')->load($product->getCustomOption('aw_sarp_subscription_type')->getValue())->getName()
            );
            if ($product->getCustomOption('aw_sarp_subscription_start')) {
                $date = new Zend_Date($product->getCustomOption('aw_sarp_subscription_start')->getValue(), 'Y-MM-dd');
                $subscription_options[] = array('label' => $startDateLabel, 'value' => $this->formatDate($date));
            }
        }

        return $subscription_options;
    }

    public function getOptionList()
    {
        return array_merge($this->_getSarpOptions(), parent::getOptionList());
    }
}
