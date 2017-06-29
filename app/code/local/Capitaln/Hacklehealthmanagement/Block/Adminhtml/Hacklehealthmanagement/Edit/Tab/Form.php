<?php

class Capitaln_Hacklehealthmanagement_Block_Adminhtml_Hacklehealthmanagement_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('hacklehealthmanagement_form', array('legend'=>Mage::helper('hacklehealthmanagement')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('hacklehealthmanagement')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('hacklehealthmanagement')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('hacklehealthmanagement')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('hacklehealthmanagement')->__('Disabled'),
              ),
          ),
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getHacklehealthmanagementData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getHacklehealthmanagementData());
          Mage::getSingleton('adminhtml/session')->setHacklehealthmanagementData(null);
      } elseif ( Mage::registry('hacklehealthmanagement_data') ) {
          $form->setValues(Mage::registry('hacklehealthmanagement_data')->getData());
      }
      return parent::_prepareForm();
  }
}