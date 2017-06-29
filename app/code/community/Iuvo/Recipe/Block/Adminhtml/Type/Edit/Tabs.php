<?php

class Iuvo_Recipe_Block_Adminhtml_Type_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('recipe_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('recipe')->__('Type'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('recipe')->__('Details'),
          'title'     => Mage::helper('recipe')->__('Details'),
          'content'   => $this->getLayout()->createBlock('recipe/adminhtml_type_edit_tab_form')->toHtml(),
      ));
      
      return parent::_beforeToHtml();
  }
}