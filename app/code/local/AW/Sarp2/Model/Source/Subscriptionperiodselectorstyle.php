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


class AW_Sarp2_Model_Source_Subscriptionperiodselectorstyle implements AW_Sarp2_Model_Source_SourceInterface
{
    const STYLE_RADIO = Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO;
    const STYLE_DROPDOWN = Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN;

    const STYLE_RADIO_LABEL = 'Radio-buttons';
    const STYLE_DROPDOWN_LABEL = 'Dropdown';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(){
        $helper = Mage::helper('aw_sarp2');

        $styleArray = array(
            self::STYLE_RADIO    => $helper->__(self::STYLE_RADIO_LABEL),
            self::STYLE_DROPDOWN => $helper->__(self::STYLE_DROPDOWN_LABEL),
        );

        return $styleArray;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(){
        $helper = Mage::helper('aw_sarp2');

        $optionArray = array(
            array(
                'value' => self::STYLE_RADIO,
                'label' => $helper->__(self::STYLE_RADIO_LABEL),
            ),
            array(
                'value' => self::STYLE_DROPDOWN,
                'label' => $helper->__(self::STYLE_DROPDOWN_LABEL),
            ),
        );

        return $optionArray;
    }
}