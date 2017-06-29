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


class AW_Sarp2_Model_Engine_Eway_Source_Unit implements AW_Sarp2_Model_Source_UnitInterface
{
    const DAY           = 1;
    const WEEK          = 2;
    const MONTH         = 3;
    const YEAR          = 4;
    const DAY_LABEL     = 'Day';
    const WEEK_LABEL    = 'Week';
    const MONTH_LABEL   = 'Month';
    const YEAR_LABEL    = 'Year';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = array();
        $preparedOptions = self::_getPreparedOptions();
        foreach (Mage::getModel('aw_sarp2/engine_eway_restrictions')->getAvailableUnitOfTime() as $unit) {
            $optionArray[] = array(
                'value' => $unit,
                'label' => isset($preparedOptions[$unit]) ? $preparedOptions[$unit] : $unit,
            );
        }
        return $optionArray;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = array();
        $preparedOptions = self::_getPreparedOptions();
        foreach (Mage::getModel('aw_sarp2/engine_eway_restrictions')->getAvailableUnitOfTime() as $unit) {
            $array[$unit] = isset($preparedOptions[$unit]) ? $preparedOptions[$unit] : $unit;
        }
        return $array;
    }

    /**
     * @return array
     */
    protected function _getPreparedOptions()
    {
        $helper = Mage::helper('aw_sarp2');
        return array(
            self::DAY       => $helper->__(self::DAY_LABEL),
            self::WEEK      => $helper->__(self::WEEK_LABEL),
            self::MONTH     => $helper->__(self::MONTH_LABEL),
            self::YEAR      => $helper->__(self::YEAR_LABEL),
        );
    }

    /**
     * Translates subscription engine period units to default units
     *
     * @param string $unit
     * @return array['unit', 'qty'] || false
     */
    public function toDefaultUnit($unit)
    {
        switch ($unit) {
            case self::DAY:
                $defaultUnit = self::DEFAULT_DAY;
                $qty = 1;
                break;
            case self::WEEK:
                $defaultUnit = self::DEFAULT_WEEK;
                $qty = 1;
                break;
            case self::MONTH:
                $defaultUnit = self::DEFAULT_MONTH;
                $qty = 1;
                break;
            case self::YEAR:
                $defaultUnit = self::DEFAULT_YEAR;
                $qty = 1;
                break;
            default:
                 $defaultUnit = false;
        }
        if ($defaultUnit) {
            $result = array ( 'unit' => $defaultUnit, 'qty' => $qty);
            return $result;
        }
        return false;
    }

}