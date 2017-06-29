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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product in category grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class AW_Sarp_Block_Adminhtml_Catalog_Product_Edit_Tab_Super_Group extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Group
{
    protected function _prepareCollection()
    {
        if ($this->_getProduct()->getTypeId() == 'subscription_grouped') {
            $allowProductTypes = array();
            foreach (Mage::getConfig()->getNode('global/catalog/product/type/subscription_grouped/allow_product_types')->children() as $type) {
                $allowProductTypes[] = $type->getName();
            }

            $collection = Mage::getModel('catalog/product_link')->useGroupedLinks()
                    ->getProductCollection()
                    ->setProduct($this->_getProduct())
                    ->addAttributeToSelect('*')
                    ->addFilterByRequiredOptions()
                    ->addAttributeToFilter('type_id', $allowProductTypes);

            $this->setCollection($collection);
            return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        }
        else
        {
            return parent::_prepareCollection();
        }
    }
}
