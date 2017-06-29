<?php

/**
 * Open Biz Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file OPEN-BIZ-LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://mageconsult.net/terms-and-conditions
 *
 * @category   Magecon
 * @package    Magecon_CustomerNotes
 * @version    1.0.1
 * @copyright  Copyright (c) 2012 Open Biz Ltd (http://www.mageconsult.net)
 * @license    http://mageconsult.net/terms-and-conditions
 */
class Magecon_CustomerNotes_Block_Adminhtml_Grid_Last extends Mage_Adminhtml_Block_Dashboard_Grid {

    public function __construct() {
        parent::__construct();
        $this->setDefaultSort('creation_time');
        $this->setDefaultDir('DESC');
        $this->setId('customernotes_grid_last_5');
    }

    protected function _prepareCollection() {
        $this->setCollection(Mage::getModel('customernotes/notes')->getCollection());
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn('customer', array(
            'header' => Mage::helper('sales')->__('Customer Name'),
            'width' => '10%',
            'sortable' => false,
            'index' => 'customer_name'
        ));

        $this->addColumn('creation_time', array(
            'header' => Mage::helper('sales')->__('Date'),
            'width' => '13%',
            'align' => 'center',
            'type' => 'datetime',
            'sortable' => false,
            'index' => 'created_time'
        ));

        $this->addColumn('added_by', array(
            'header' => Mage::helper('sales')->__('Added By'),
            'width' => '7%',
            'align' => 'left',
            'sortable' => false,
            'index' => 'username'
        ));

        $this->addColumn('note', array(
            'header' => Mage::helper('sales')->__('Note'),
            'align' => 'left',
            'sortable' => false,
            'index' => 'note'
        ));

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/customer/edit', array('id' => $row->getCustomerId()));
    }

}