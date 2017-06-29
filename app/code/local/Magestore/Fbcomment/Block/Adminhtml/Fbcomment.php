<?php
class Magestore_Fbcomment_Block_Adminhtml_Fbcomment extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_fbcomment';
    $this->_blockGroup = 'fbcomment';
    $this->_headerText = Mage::helper('fbcomment')->__('Item Manager');
    $this->_addButtonLabel = Mage::helper('fbcomment')->__('Add Item');
    parent::__construct();
  }
}