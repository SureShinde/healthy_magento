<?php

class Magestore_Fbcomment_Block_Adminhtml_Fbcomment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('fbcomment_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('fbcomment')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('fbcomment')->__('Item Information'),
          'title'     => Mage::helper('fbcomment')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('fbcomment/adminhtml_fbcomment_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}