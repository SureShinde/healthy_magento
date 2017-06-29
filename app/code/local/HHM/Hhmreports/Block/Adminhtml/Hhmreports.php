<?php
class HHM_Hhmreports_Block_Adminhtml_Hhmreports extends Mage_Adminhtml_Block_Widget_Grid_Container {
 
    public function __construct() {
        $this->_controller = 'adminhtml_hhmreports';
        $this->_blockGroup = 'hhmreports';
        $this->_headerText = Mage::helper('hhmreports')->__('Hhmreports');
        parent::__construct();
        $this->_removeButton('add');
    }
 
}