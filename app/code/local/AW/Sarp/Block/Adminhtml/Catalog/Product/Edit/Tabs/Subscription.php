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

class AW_Sarp_Block_Adminhtml_Catalog_Product_Edit_Tabs_Subscription extends Mage_Adminhtml_Block_Widget implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Reference to product objects that is being edited
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;

    protected $_config = null;

    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Enable subscriptions');
    }

    public function getTabTitle()
    {
        return $this->__('Enable subscriptions');
    }


    public function canShowTab()
    {
        $productType = Mage::registry('product')->getTypeId();
        if (
            in_array($productType, $this->_getAvailableMagentoProductTypes()) ||
            in_array($productType, $this->_getAvailableSarpProductTypes())
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check if tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Render block HTML
     *
     * @return string
     */


    protected function _toHtml()
    {

        $id = $this->getRequest()->getParam('id');

        try {
            $productType = Mage::registry('product')->getTypeId();
            if (in_array($productType, $this->_getAvailableMagentoProductTypes())) {
                $label = 'Convert this product to subscription';
            }
            else{
                $label = 'Convert this subscription product to standard type';
            }

            $button = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setClass('add')
                    ->setType('button')
                    ->setOnClick('window.location.href=\'' . $this->getUrl('sarp_admin/product/convert', array('id' => $id)) . '\'')
                    ->setLabel($label);
            return $button->toHtml();
        } catch (exception $e) {
            return $this->__("Sorry, but this product cannot have a subscription");
        }
    }

    protected function _getAvailableSarpProductTypes()
    {
        $types = array(
            AW_Sarp_Model_Product_Type_Configurable_Subscription::PRODUCT_TYPE_CONFIGURABLE,
            AW_Sarp_Model_Product_Type_Downloadable_Subscription::PRODUCT_TYPE_DOWLOADABLE,
            AW_Sarp_Model_Product_Type_Grouped_Subscription::TYPE_CODE,
            AW_Sarp_Model_Product_Type_Simple_Subscription::TYPE_CODE,
        );
        return $types;
    }

    protected function _getAvailableMagentoProductTypes()
    {
        $types = array(
            Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
            Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
            Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE,
            Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL,
        );
        return $types;
    }


}
