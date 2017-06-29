<?php

class Iuvo_Recipe_Block_Adminhtml_Recipe_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('recipe_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('recipe')->__('Recipe'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('recipe')->__('Details'),
          'title'     => Mage::helper('recipe')->__('Details'),
          'content'   => $this->getLayout()->createBlock('recipe/adminhtml_recipe_edit_tab_form')->toHtml(),
      ));
      $this->addTab('meta_section', array(
          'label'     => Mage::helper('recipe')->__('Meta Information'),
          'title'     => Mage::helper('recipe')->__('Meta Information'),
          'content'   => $this->getLayout()->createBlock('recipe/adminhtml_recipe_edit_tab_meta')->toHtml(),
      ));
      
      $this->addTab('ingredients_section', array(
          'label'     => Mage::helper('recipe')->__('Ingredients'),
          'title'     => Mage::helper('recipe')->__('Ingredients'),
          'content'   => $this->getLayout()->createBlock('recipe/adminhtml_recipe_edit_tab_ingredient')->toHtml(),
      ));
      $this->addTab('steps_section', array(
          'label'     => Mage::helper('recipe')->__('Steps'),
          'title'     => Mage::helper('recipe')->__('Steps'),
          'content'   => $this->getLayout()->createBlock('recipe/adminhtml_recipe_edit_tab_step')->toHtml(),
      ));
/*
      $this->addTab('product_section', array(
          'label'     => Mage::helper('recipe')->__('Products'),
          'title'     => Mage::helper('recipe')->__('Products'),
          'content'   => $this->getLayout()->createBlock('recipe/adminhtml_recipe_edit_tab_productfield')->toHtml(),
      ));
*/
      $this->addTab('review_section', array(
          'label'     => Mage::helper('recipe')->__('Reviews'),
          'title'     => Mage::helper('recipe')->__('Reviews'),
          'content'   => $this->getLayout()->createBlock('recipe/adminhtml_recipe_edit_tab_review')->toHtml(),
      ));
      

      
      return parent::_beforeToHtml();
  }
}