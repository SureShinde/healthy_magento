<?php

class Iuvo_Recipe_Block_Adminhtml_Submit_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('submit_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('recipe')->__('Recipe Submission'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('recipe')->__('Details'),
          'title'     => Mage::helper('recipe')->__('Details'),
          'content'   => $this->getLayout()->createBlock('recipe/adminhtml_submit_edit_tab_form')->toHtml(),
      ));
      
      
/*
      $this->addTab('products_section', array(
          'label'     => Mage::helper('recipe')->__('Products'),
          'title'     => Mage::helper('recipe')->__('Products'),
          'content'   => $this->getLayout()->createBlock('adminhtml/catalog_product_widget_chooser','' ,array(
	            'use_massaction' => true,
	        ))->toHtml(),
      ));
*/
      return parent::_beforeToHtml();
  }
}