<?php
/**
 * Abandoned Carts Alerts Pro
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     G6aOfhcOapJAn8TGHRhFIbUCRgt8VW93TqhNqwDF4t
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
class AdjustWare_Cartalert_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getGroupArray()
    {
        $db = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $db->select()->from(Mage::getSingleton('core/resource')->getTableName('customer/customer_group'), array('customer_group_id', 'customer_group_code'));
        $groupIds = array();
        foreach($db->fetchAll($select) as $group)
        {
            $groupIds[$group['customer_group_id']] = $group['customer_group_code'];
        }
        return $groupIds;
    }
}