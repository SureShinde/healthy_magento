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

class AW_Sarp2_Block_Adminhtml_Notification_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('notificationsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);

    }

    protected function _prepareCollection()
    {
        //$collection = Mage::getResourceModel('aw_sarp2/notification_collection');
        $collection = Mage::getModel('aw_sarp2/notification')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array(
                'header' => $this->__('ID'),
                'type'  => 'number',
                'align' => 'right',
                'width' => '5',
                'index' => 'entity_id',
                'filter_index' => 'main_table.entity_id',
            )
        );

        $this->addColumn('name', array(
            'header' => $this->__('Name'),
            'align' => 'left',
            'index' => 'name',
        ));

        $this->addColumn('type', array(
            'header' => $this->__('Event type'),
            'align' => 'left',
            'index' => 'type',
            'type' => 'options',
            'options' => Mage::getModel('aw_sarp2/source_notification_type')->getGridOptions(),
        ));

        $this->addColumn('recipient', array(
            'header' => $this->__('Recipient'),
            'align' => 'left',
            'index' => 'recipient',
            'type' => 'options',
            'options' => Mage::getModel('aw_sarp2/source_notification_recipient')->getGridOptions(),
        ));

        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'align' => 'left',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getModel('aw_sarp2/source_notification_status')->getGridOptions(),
        ));


        $this->addColumn('action',
            array(
                'header' => $this->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(

                    array(
                        'caption' => $this->__('Edit'),
                        'url' => array('base' => '*/*/edit'),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'is_system' => true,
            ));

        $ret = parent::_prepareColumns();

        return $ret;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit/', array('id' => $row->getId()));
    }

}