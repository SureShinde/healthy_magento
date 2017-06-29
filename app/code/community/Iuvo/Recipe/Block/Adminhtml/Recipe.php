<?php
class Iuvo_Recipe_Block_Adminhtml_Recipe extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_recipe';
    $this->_blockGroup = 'recipe';
    $this->_headerText = Mage::helper('recipe')->__('Manage Recipes');
    $this->_addButtonLabel = Mage::helper('recipe')->__('Add New Recipe');
    parent::__construct();
  }
}