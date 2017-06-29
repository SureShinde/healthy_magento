<?php

class Capitaln_Hacklehealthmanagement_Block_Adminhtml_Hacklehealthmanagement_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'hacklehealthmanagement';
        $this->_controller = 'adminhtml_hacklehealthmanagement';
        
        $this->_updateButton('save', 'label', Mage::helper('hacklehealthmanagement')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('hacklehealthmanagement')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('hacklehealthmanagement_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'hacklehealthmanagement_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'hacklehealthmanagement_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('hacklehealthmanagement_data') && Mage::registry('hacklehealthmanagement_data')->getId() ) {
            return Mage::helper('hacklehealthmanagement')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('hacklehealthmanagement_data')->getTitle()));
        } else {
            return Mage::helper('hacklehealthmanagement')->__('Add Item');
        }
    }
}