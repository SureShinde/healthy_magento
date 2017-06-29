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

class AW_Sarp_ProductController extends Mage_Adminhtml_Controller_Action
{
    public function convertAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('id'));
                $old_type = $product->getTypeId();
                if (in_array($old_type, $this->__getAvailableMagentoProductTypes())) {
                    $this->_convertToSarp($product);
                }
                elseif(in_array($old_type, $this->__getAvailableSarpProductTypes())) {
                    $this->_convertToStandard($product);
                }
                else {
                    throw new Mage_Core_Exception("The product type $old_type can't be converted");
                }
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirectReferer();
    }

    protected function _convertToSarp($product)
    {
        $old_type = $product->getTypeId();
        $new_type = 'subscription_' . $old_type;
        $this->__beforeConvert($product);
        $product->setTypeId($new_type)->save();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('sarp')->__('Product was successfully converted to subscription'));
    }

    protected function _convertToStandard($product)
    {
        //TODO: check on subscription with this product
        $old_type = $product->getTypeId();
        $new_type = str_replace('subscription_', '', $old_type);
        $product->setTypeId($new_type)->save();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('sarp')->__('Subscription product was successfully converted to standard type'));
    }

    private function __beforeConvert($product)
    {
        //if converted from GROUPED
        if ($product->isGrouped()) {
            $product->setGroupedLinkData(array());
        }
        return $this;
    }


    private function __getAvailableSarpProductTypes()
    {
        $types = array(
            AW_Sarp_Model_Product_Type_Configurable_Subscription::PRODUCT_TYPE_CONFIGURABLE,
            AW_Sarp_Model_Product_Type_Downloadable_Subscription::PRODUCT_TYPE_DOWLOADABLE,
            AW_Sarp_Model_Product_Type_Grouped_Subscription::TYPE_CODE,
            AW_Sarp_Model_Product_Type_Simple_Subscription::TYPE_CODE,
        );
        return $types;
    }

    private function __getAvailableMagentoProductTypes()
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