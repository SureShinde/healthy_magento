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


class AW_Sarp2_Model_Engine_Eway_Source_Status implements AW_Sarp2_Model_Source_SourceInterface
{
    const ACTIVE     = 'active';
    const CANCELLED  = 'cancelled';
    const EXPIRED    = 'expired';
    const ACTIVE_LABEL     = 'Active';
    const CANCELLED_LABEL  = 'Cancelled';
    const EXPIRED_LABEL    = 'Expired';

    protected static $_globalStatusMap = array(
        AW_Sarp2_Model_Source_Profile_Status::ACTIVE    => self::ACTIVE,
        AW_Sarp2_Model_Source_Profile_Status::CANCELLED => self::CANCELLED,
    );

    protected static $_nonGlobalStatusMap = array(
        self::EXPIRED => self::EXPIRED_LABEL,
    );

    /**
     * @return array
     */
    protected function _getPreparedOptions()
    {
        $helper = Mage::helper('aw_sarp2');
        return array(
            self::ACTIVE    => $helper->__(self::ACTIVE_LABEL),
            self::CANCELLED => $helper->__(self::CANCELLED_LABEL),
            self::EXPIRED   => $helper->__(self::EXPIRED_LABEL),
        );
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = array();
        $preparedOptions = self::_getPreparedOptions();
        foreach (Mage::getModel('aw_sarp2/engine_eway_restrictions')->getAvailableSubscriptionStatus() as $status) {
            $optionArray[] = array(
                'value' => $status,
                'label' => isset($preparedOptions[$status]) ? $preparedOptions[$status] : $status,
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
        foreach (Mage::getModel('aw_sarp2/engine_eway_restrictions')->getAvailableSubscriptionStatus() as $status) {
            $array[$status] = isset($preparedOptions[$status]) ? $preparedOptions[$status] : $status;
        }
        return $array;
    }

    /**
     * @param string $statusCode
     *
     * @return null|string
     */
    public function getStatusLabel($statusCode)
    {
        $statusArray = $this->toArray();
        if (isset($statusArray[$statusCode])) {
            return $statusArray[$statusCode];
        }
        return null;
    }

    /**
     * @return array
     */
    public function getGlobalStatusMap()
    {
        return self::$_globalStatusMap;
    }

    /**
     * @return array
     */
    public function getNonGlobalStatusMap()
    {
        return self::$_nonGlobalStatusMap;
    }
}