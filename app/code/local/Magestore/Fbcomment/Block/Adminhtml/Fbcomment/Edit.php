<?php

class Magestore_Fbcomment_Block_Adminhtml_Fbcomment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'fbcomment';
        $this->_controller = 'adminhtml_fbcomment';
        
        $this->_updateButton('save', 'label', Mage::helper('fbcomment')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('fbcomment')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('fbcomment_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'fbcomment_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'fbcomment_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('fbcomment_data') && Mage::registry('fbcomment_data')->getId() ) {
            return Mage::helper('fbcomment')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('fbcomment_data')->getTitle()));
        } else {
            return Mage::helper('fbcomment')->__('Add Item');
        }
    }
}