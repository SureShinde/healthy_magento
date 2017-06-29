<?php
class Iuvo_Recipe_Block_Adminhtml_Recipe_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('recipe_form', array('legend'=>Mage::helper('recipe')->__('Recipe Details')));


      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('recipe')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('recipe')->__('In Draft'),
              ),
              array(
                  'value'     => 2,
                  'label'     => Mage::helper('recipe')->__('Published'),
              ),
          ),
      ));

     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('recipe')->__('Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));
      
      $fieldset->addField('url_key', 'text', array(
          'label'     => Mage::helper('recipe')->__('URL Key'),
          'name'      => 'url_key',
      ));
      
	  $fieldset->addField('desc', 'textarea', array(
          'label'     => Mage::helper('recipe')->__('Description'),
          'required'  => true,
          'name'      => 'desc',
      ));
      $fieldset->addField('servings', 'text', array(
          		'name' 		=>'servings',
          		'label' 	=> Mage::helper('recipe')->__('Servings'),
          		'title'     => Mage::helper('recipe')->__('Servings'),
          		'required'  => true,
          		'style' 	=> 'width: 70px;'
      ));
      $fieldset->addField('prep_time', 'text', array(
          		'name' 		=>'prep_time',
          		'label' 	=> Mage::helper('recipe')->__('Preparation Time'),
          		'title'     => Mage::helper('recipe')->__('Preparation Time'),
          		'required'  => true,
          		'style' 	=> 'width: 70px;'
      ));
      $fieldset->addField('difficulty', 'select', array(
          'label'     => Mage::helper('recipe')->__('Difficulty'),
          'name'      => 'difficulty',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('recipe')->__('Easy'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('recipe')->__('Moderate'),
              ),
              
              array(
                  'value'     => 3,
                  'label'     => Mage::helper('recipe')->__('Hard'),
              ),
          ),
      ));
      
      $col = Mage::getModel('recipe/type')->getCollection();
      $types = array();
      foreach($col as $type) {
      	$types[] = array('value'=>$type->getId(), 'label'=>$type->getDishtype());
      }
      $fieldset->addField('dishtype', 'select', array(
          'label'     => Mage::helper('recipe')->__('Dish Type'),
          'name'      => 'dishtype',
          'values'    => $types,
      ));
      
      $fieldset->addField('featured', 'select', array(
          'label'     => Mage::helper('recipe')->__('Featured'),
          'name'      => 'featured',
          'values'    => array(
              array(
                  'value'     => 0,
                  'label'     => Mage::helper('recipe')->__('No'),
              ),

              array(
                  'value'     => 1,
                  'label'     => Mage::helper('recipe')->__('Yes'),
              ),
          ),
      ));
      
      $fieldset->addField('skus', 'text', array(
          		'name' 		=>'skus',
          		'label' 	=> Mage::helper('recipe')->__('Product SKUs in this recipe'),
          		'title'     => Mage::helper('recipe')->__('Product SKUs in this recipe'),
      ));
	  $fieldset->addField('filename', 'file', array(
	  		'name' 		=>'filename',
          	'label' 	=> Mage::helper('recipe')->__('Image (JPG ONLY!!)'),
          	'title'     => Mage::helper('recipe')->__('Image'),
          	'required'  => false
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