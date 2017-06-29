<?php
class Capitaln_Hacklehealthmanagement_Block_Adminhtml_Hacklehealthmanagement extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_hacklehealthmanagement';
    $this->_blockGroup = 'hacklehealthmanagement';
    $this->_headerText = Mage::helper('hacklehealthmanagement')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('hacklehealthmanagement')->__('Add Item');
    parent::__construct();
  }
}