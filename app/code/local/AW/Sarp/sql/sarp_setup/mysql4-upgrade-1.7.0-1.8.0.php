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


$installer = $this;
$installer->startSetup();
$this->_setupCache = array();
$attributes = array(
                    'aw_sarp_subscription_price',
                    'aw_sarp_first_period_price',
                );
$this->_setupCache = array();
try {
    foreach($attributes as $attribute) {
        $applyTo = explode(',', $installer->getAttribute('catalog_product', $attribute, 'apply_to'));
        foreach($applyTo as $key => $type) {
            if ($type === 'subscription_grouped') {
                unset($applyTo[$key]);
            }
        }
        $installer->updateAttribute('catalog_product', $attribute, 'apply_to', implode(',', $applyTo));
    }
} catch (Exception $e)
{

}

$installer->endSetup();