<?php

class Magestore_Fbcomment_Block_Adminhtml_Fbcomment_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('fbcomment_form', array('legend'=>Mage::helper('fbcomment')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('fbcomment')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('fbcomment')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('fbcomment')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('fbcomment')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('fbcomment')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('fbcomment')->__('Content'),
          'title'     => Mage::helper('fbcomment')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getFbcommentData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getFbcommentData());
          Mage::getSingleton('adminhtml/session')->setFbcommentData(null);
      } elseif ( Mage::registry('fbcomment_data') ) {
          $form->setValues(Mage::registry('fbcomment_data')->getData());
      }
      return parent::_prepareForm();
  }
}