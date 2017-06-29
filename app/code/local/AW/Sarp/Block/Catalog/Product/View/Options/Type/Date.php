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


class AW_Sarp_Block_Catalog_Product_View_Options_Type_Date extends Mage_Catalog_Block_Product_View_Options_Type_Date
{
    /**
     * HTML select element
     *
     * @param string $name Id/name of html select element
     * @return Mage_Core_Block_Html_Select
     */
    protected function _getHtmlSelect($name, $value = null)
    {
        $option = $this->getOption();

        // $require = $this->getOption()->getIsRequire() ? ' required-entry' : '';
        $require = '';
        $select = $this->getLayout()->createBlock('core/html_select')
                ->setId('options_' . $this->getOption()->getId() . '_' . $name)
                ->setClass('product-custom-option datetime-picker' . $require)
                ->setExtraParams()
                ->setName('options[' . $option->getId() . ']' . '[' . $name . ']');

        $extraParams = 'style="width:auto"';
        if (!$this->getSkipJsReloadPrice()) {
            $extraParams .= ' onchange="opConfig.reloadPrice()"';
        }
        $select->setExtraParams($extraParams);

        if (is_null($value)) {
            $value = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId() . '/' . $name);
        }
        if (!is_null($value)) {
            $select->setValue($value);
        }

        return $select;
    }
}
