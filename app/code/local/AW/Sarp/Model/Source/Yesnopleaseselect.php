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

class AW_Sarp_Model_Source_Yesnopleaseselect extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    const YES = 2;
    const NO = 1;
    const NONE = "";

    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array(
                array(
                    'label' => Mage::helper('sarp')->__('-- Please Select --'),
                    'value' => self::NONE
                ),
                array(
                    'label' => Mage::helper('sarp')->__('Yes'),
                    'value' => self::YES
                ),
                array(
                    'label' => Mage::helper('sarp')->__('No'),
                    'value' => self::NO
                ),
            );
        }
        return $this->_options;
    }
}