<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @copyright  Copyright (c) 2012 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */

class MageWorx_OrdersPro_Model_Mysql4_Order_Collection extends Mage_Sales_Model_Mysql4_Order_Collection
{
    
    public function setFilterOrdersNoGroup($days = 0) {              
        if ($this->getSelect()!==null) {                        
            $where  = 'order_item_group_tbl.`order_group_id` IS NULL';
            if ($days > 0) $where.=  ' AND main_table.`created_at` <= (NOW() - INTERVAL '.$days.' DAY)';
            
            $this->getSelect()->joinLeft(array('order_item_group_tbl'=>$this->getTable('orderspro/order_item_group')),
                    'order_item_group_tbl.order_id = main_table.entity_id',                    
                    'order_group_id')
                ->where(new Zend_Db_Expr($where))                
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(new Zend_Db_Expr('main_table.`entity_id`'));            
        }
        return $this;
    }        
    
    public function hideDeletedGroup()
    {              
        if ($this->getSelect()!==null) {                        
            $this->getSelect()
                ->joinLeft(array('order_item_group_tbl'=>$this->getTable('orderspro/order_item_group')),
                'order_item_group_tbl.order_id = main_table.entity_id',
                 'order_group_id')
                ->where('order_item_group_tbl.`order_group_id` IS NULL OR order_item_group_tbl.`order_group_id`=1');
        }                        
        return $this;
    }
    
}
