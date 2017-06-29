<?php
class HHM_Hhmreports_Block_Adminhtml_Hhmreports_Grid extends Mage_Adminhtml_Block_Report_Grid {
 
    public function __construct() {
        parent::__construct();
        $this->setId('hhmreportsGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setSubReportSize(false);
    }
 
    protected function _prepareCollection() {
        parent::_prepareCollection();
        $this->getCollection()->initReport('hhmreports/hhmreports');
        return $this;
    }
 
    protected function _prepareColumns() {
        $this->addColumn('ordered_qty', array(
            'header'    =>Mage::helper('reports')->__('Product Name'),
            'align'     =>'left',
            'index'     =>'ordered_qty',
            'total'     =>'sum',
            'type'      =>'string'
        ));
        $this->addColumn('item_id', array(
            'header' => Mage::helper('reports')->__('Quantity Ordered'),
            'align' => 'right',
            'index' => 'ordered_qty',
            'type'  => 'number',
            'total' => 'sum',
        ));
        $this->addExportType('*/*/exportCsv', Mage::helper('hhmreports')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('hhmreports')->__('XML'));
        return parent::_prepareColumns();
    }
 
    public function getRowUrl($row) {
        return false;
    }
 
    public function getReport($from, $to) {
        if ($from == '') {
            $from = $this->getFilter('report_from');
        }
        if ($to == '') {
            $to = $this->getFilter('report_to');
        }
        $totalObj = Mage::getModel('reports/totals');
        $totals = $totalObj->countTotals($this, $from, $to);
        $this->setTotals($totals);
        $this->addGrandTotals($totals);
        return $this->getCollection()->getReport($from, $to);
    }
}