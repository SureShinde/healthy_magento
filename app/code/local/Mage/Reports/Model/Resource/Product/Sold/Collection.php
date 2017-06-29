<?php
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
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Report Sold Products collection
 *
 * @category    Mage
 * @package     Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Reports_Model_Resource_Product_Sold_Collection extends Mage_Reports_Model_Resource_Product_Collection
{
    /**
     * Initialize resources
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_useAnalyticFunction = true;
    }
    /**
     * Set Date range to collection
     *
     * @param int $from
     * @param int $to
     * @return Mage_Reports_Model_Resource_Product_Sold_Collection
     */
    public function setDateRange($from, $to)
    {
        $this->_reset()
            ->addAttributeToSelect('*')
            ->addOrderedQty($from, $to)
            ->setOrder('ordered_qty', self::SORT_ORDER_DESC);
        return $this;
    }

    /**
     * Set store filter to collection
     *
     * @param array $storeIds
     * @return Mage_Reports_Model_Resource_Product_Sold_Collection
     */
    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->getSelect()->where('order_items.store_id IN (?)', (array)$storeIds);
        }
        return $this;
    }

    /**
     * Add website product limitation
     *
     * @return Mage_Reports_Model_Resource_Product_Sold_Collection
     */
    protected function _productLimitationJoinWebsite()
    {
        $filters     = $this->_productLimitationFilters;
        $conditions  = array('product_website.product_id=e.entity_id');
        if (isset($filters['website_ids'])) {
            $conditions[] = $this->getConnection()
                ->quoteInto('product_website.website_id IN(?)', $filters['website_ids']);

            $subQuery = $this->getConnection()->select()
                ->from(array('product_website' => $this->getTable('catalog/product_website')),
                    array('product_website.product_id')
                )
                ->where(join(' AND ', $conditions));
            $this->getSelect()->where('e.entity_id IN( '.$subQuery.' )');
        }

        return $this;
    }
	
	public function addOrderedQty($from = '', $to = '')
    {
        $adapter              = $this->getConnection();
        $compositeTypeIds     = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
        $orderTableAliasName  = $adapter->quoteIdentifier('order');

        $orderJoinCondition   = array(
            $orderTableAliasName . '.entity_id = order_items.order_id',
            $adapter->quoteInto("{$orderTableAliasName}.status = ?", Mage_Sales_Model_Order::STATE_COMPLETE),

        );

        $productJoinCondition = array(
            $adapter->quoteInto('(e.type_id NOT IN (?))', $compositeTypeIds),
            'e.entity_id = order_items.product_id',
            $adapter->quoteInto('e.entity_type_id = ?', $this->getProductEntityTypeId())
        );

        if ($from != '' && $to != '') {
            $fieldName            = $orderTableAliasName . '.updated_at';
            $orderJoinCondition[] = $this->_prepareBetweenSql($fieldName, $from, $to);
        }

        $this->getSelect()->reset()
            ->from(
                array('order_items' => $this->getTable('sales/order_item')),
                array(
                    'ordered_qty' => 'SUM(order_items.qty_ordered)',
					'row_total' => 'SUM(order_items.row_total)',
					//'row_total' => 'SUM(order_items.original_price * order_items.qty_ordered)',
					'tax' => 'SUM(order_items.tax_amount)',
					'discount' => 'SUM(order_items.discount_amount)',
					'order_items_name' => 'order_items.name'
                ))
            ->joinInner(
                array('order' => $this->getTable('sales/order')),
                implode(' AND ', $orderJoinCondition),
                array(
					'shipped' => 'SUM(order.shipping_amount)'))
            ->joinLeft(
                array('e' => $this->getProductEntityTableName()),
                implode(' AND ', $productJoinCondition),
                array(
                    'entity_id' => 'order_items.product_id',
                    'entity_type_id' => 'e.entity_type_id',
                    'attribute_set_id' => 'e.attribute_set_id',
                    'type_id' => 'e.type_id',
                    'sku' => 'e.sku',
                    'has_options' => 'e.has_options',
                    'required_options' => 'e.required_options',
                    'created_at' => 'e.created_at',
                    'updated_at' => 'e.updated_at'
                ))
            ->where('parent_item_id IS NULL')
			->group('order_items.product_id')
            ->having('SUM(order_items.qty_ordered) > ?', 0);
			//echo "<pre>"; print_r($this); exit;
        return $this;
    }
}
