<?php

class Capitaln_Hacklehealthmanagement_Block_Adminhtml_Hacklehealthmanagement_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('hacklehealthmanagement_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('hacklehealthmanagement')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('hacklehealthmanagement')->__('Item Information'),
          'title'     => Mage::helper('hacklehealthmanagement')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('hacklehealthmanagement/adminhtml_hacklehealthmanagement_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}