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
$installer->addAttribute('catalog_product', 'aw_sarp_display_calendar', array(
                              'backend' => '',
                              'frontend' => '',
                              'source' => 'sarp/source_yesnopleaseselect',
                              'group' => 'Subscription',
                              'label' => 'Show "First delivery" calendar at product page',
                              'input' => 'select',
                              'class' => '',
                              'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
                              'type' => 'int',
                              'visible' => 1,
                              'user_defined' => false,
                              'default' => AW_Sarp_Model_Source_Yesnopleaseselect::NONE,
                              'apply_to' => 'subscription_simple,subscription_downloadable,subscription_configurable,subscription_grouped',
                              'visible_on_front' => false
                         ));
$this->_setupCache = array();
$virtualTypeCode = 'subscription_virtual';
$allAttributes = array(
                    'aw_sarp_enabled',
                    'aw_sarp_period',
                    'aw_sarp_subscription_price',
                    'aw_sarp_first_period_price',
                    'aw_sarp_include_to_group',
                    'aw_sarp_exclude_to_group',
                    'postman_notice',
//                  'aw_sarp_shipping_cost',
                    'aw_sarp_anonymous_subscription',
                    'aw_sarp_options_to_first_price',
                    'aw_sarp_display_calendar'
                );

$allAttributes = array_merge($allAttributes, array('price', 'special_price', 'special_from_date', 'special_to_date',
                   'minimal_price', 'cost', 'tier_price', 'tax_class_id',
                   'shoe_type', 'default_category_id', 'giftcert_code', 'image', 'shape',
                   'in_depth', 'dimension', 'model', 'activation_information', 'memory',
                   'hardrive', 'screensize', 'gender', 'shoe_size', 'country_orgin', 'room',
                   'finish', 'computer_manufacturers', 'megapixels', 'shirt_size', 'gift_message_available'));
$this->_setupCache = array();
try {
    foreach($allAttributes as $attribute) {
        $applyTo = explode(',', $installer->getAttribute('catalog_product', $attribute, 'apply_to'));
        if (!in_array($virtualTypeCode, $applyTo)) {
            $applyTo[] = $virtualTypeCode;
            $installer->updateAttribute('catalog_product', $attribute, 'apply_to', implode(',', $applyTo));
        }
    }
} catch (Exception $e)
{

}

$installer->endSetup();