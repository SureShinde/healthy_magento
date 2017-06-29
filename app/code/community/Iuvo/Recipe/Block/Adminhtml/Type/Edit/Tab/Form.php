<?php
class Iuvo_Recipe_Block_Adminhtml_Type_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('recipe_form', array('legend'=>Mage::helper('recipe')->__('Type Details')));

     
      $fieldset->addField('dishtype', 'text', array(
          'label'     => Mage::helper('recipe')->__('Dishtype'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'dishtype',
      ));
	        
      if ( Mage::getSingleton('adminhtml/session')->getRecipesData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getRecipesData());
          Mage::getSingleton('adminhtml/session')->setRecipesData(null);
      } elseif ( Mage::registry('recipe_data') ) {
          $form->setValues(Mage::registry('recipe_data')->getData());
      }
      return parent::_prepareForm();
  }
}