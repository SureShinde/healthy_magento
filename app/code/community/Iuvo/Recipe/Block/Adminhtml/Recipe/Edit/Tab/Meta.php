<?php
class Iuvo_Recipe_Block_Adminhtml_Recipe_Edit_Tab_Meta extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('recipe_form', array('legend'=>Mage::helper('recipe')->__('Meta Data')));

      $fieldset->addField('meta_title', 'text', array(
          'label'     => Mage::helper('recipe')->__('Meta Title'),
          'name'      => 'meta_title',
      ));
	  $fieldset->addField('meta_keywords', 'textarea', array(
          'label'     => Mage::helper('recipe')->__('Meta Keywords'),
          'name'      => 'meta_keywords',
      ));
	  $fieldset->addField('meta_description', 'textarea', array(
          'label'     => Mage::helper('recipe')->__('Meta Description'),
          'name'      => 'meta_description',
      ));
      
      if(Mage::getSingleton('adminhtml/session')->getRecipesData()) {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getRecipesData());
          Mage::getSingleton('adminhtml/session')->setRecipesData(null);
      } elseif(Mage::registry('recipe_data')) {
          $form->setValues(Mage::registry('recipe_data')->getData());
      }
      return parent::_prepareForm();
  }
}