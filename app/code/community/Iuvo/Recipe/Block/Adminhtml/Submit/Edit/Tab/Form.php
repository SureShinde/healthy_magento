<?php
class Iuvo_Recipe_Block_Adminhtml_Submit_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('submit_form', array('legend'=>Mage::helper('recipe')->__('Recipe Submission Details')));


     
      $fieldset->addField('fname', 'text', array(
          'label'     => Mage::helper('recipe')->__('FirstName'),
          'name'      => 'fname',
      ));
      $fieldset->addField('lname', 'text', array(
          'label'     => Mage::helper('recipe')->__('Last Name'),
          'name'      => 'lname',
      ));
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('recipe')->__('Title'),
          'name'      => 'title',
      ));
      $fieldset->addField('email', 'text', array(
          'label'     => Mage::helper('recipe')->__('Email'),
          'name'      => 'email',
      ));
      
	  $fieldset->addField('desc', 'textarea', array(
          'label'     => Mage::helper('recipe')->__('Description'),
          'required'  => true,
          'name'      => 'desc',
      ));
      
      $fieldset->addField('ingredients', 'textarea', array(
          'label'     => Mage::helper('recipe')->__('Ingredients'),
          'required'  => true,
          'name'      => 'ingredients',
      ));
      
      $fieldset->addField('directions', 'textarea', array(
          'label'     => Mage::helper('recipe')->__('Directions'),
          'required'  => true,
          'name'      => 'directions',
      ));
      
      $fieldset->addField('type', 'text', array(
          'label'     => Mage::helper('recipe')->__('User Submitted Dish Type'),
          'name'      => 'type',
      ));
      
      $fieldset->addField('servings', 'text', array(
          'label'     => Mage::helper('recipe')->__('Servings'),
          'name'      => 'servings',
      ));
      
      $fieldset->addField('prep_time', 'text', array(
          'label'     => Mage::helper('recipe')->__('Prep Time'),
          'name'      => 'prep_time',
      ));
      

	  $fieldset->addField('image_path', 'image', array(
	  		'name' 		=>'image_path',
          	'label' 	=> Mage::helper('recipe')->__('Image'),
          	'title'     => Mage::helper('recipe')->__('Image'),
	  ));
      
      if ( Mage::getSingleton('adminhtml/session')->getRecipesData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getRecipesData());
          Mage::getSingleton('adminhtml/session')->setRecipesData(null);
      } elseif ( Mage::registry('submit_data') ) {
          $form->setValues(Mage::registry('submit_data')->getData());
      }
      $p = $form->getElement('image_path')->getValue();
  	  $form->getElement('image_path')->setValue(Mage::getBaseUrl('web') . 'media/recipe/usersubmit/' . $p);

      return parent::_prepareForm();
  }
}