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

class AW_Sarp2_Model_Source_Notification_Type extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{

    /**
     * Retrive all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }

    public function toOptionArray()
    {
        return array(
            array('value' => AW_Sarp2_Model_Notification::TYPE_NEW_PROFILE,                 'label' => Mage::helper('aw_sarp2')->__('New profile created')),
            array('value' => AW_Sarp2_Model_Notification::TYPE_CHANGED_PROFILE_STATUS,      'label' => Mage::helper('aw_sarp2')->__('Profile status changed')),
            array('value' => AW_Sarp2_Model_Notification::TYPE_NEW_ORDER,                   'label' => Mage::helper('aw_sarp2')->__('New order created')),
            array('value' => AW_Sarp2_Model_Notification::TYPE_PROFILE_ABOUT_TO_EXPIRE,     'label' => Mage::helper('aw_sarp2')->__('Profile expired')),
            array('value' => AW_Sarp2_Model_Notification::TYPE_PROFILE_PAYMENT ,            'label' => Mage::helper('aw_sarp2')->__('Next payment')),
        );
    }

    /**
     * Returns label for value
     * @param string $value
     * @return string
     */
    public function getLabel($value)
    {
        $options = $this->toOptionArray();
        foreach ($options as $v) {
            if ($v['value'] == $value) {
                return $v['label'];
            }
        }
        return '';
    }

    /**
     * Returns array ready for use by grid
     * @return array
     */
    public function getGridOptions()
    {
        $items = $this->getAllOptions();
        $out = array();
        foreach ($items as $item) {
            $out[$item['value']] = $item['label'];
        }
        return $out;
    }

}
