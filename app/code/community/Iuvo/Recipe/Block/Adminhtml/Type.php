<?php
class Iuvo_Recipe_Block_Adminhtml_Type extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_type';
    $this->_blockGroup = 'recipe';
    $this->_headerText = Mage::helper('recipe')->__('Manage Dish Types');
    $this->_addButtonLabel = Mage::helper('recipe')->__('Add New Dish Type');
    parent::__construct();
  }
}