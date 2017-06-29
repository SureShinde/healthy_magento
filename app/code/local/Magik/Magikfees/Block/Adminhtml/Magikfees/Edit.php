<?php

class Magik_Magikfees_Block_Adminhtml_Magikfees_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();

		$this->_objectId = 'id';
		$this->_blockGroup = 'magikfees';
		$this->_controller = 'adminhtml_magikfees';

		$this->_updateButton('save', 'label', Mage::helper('magikfees')->__('Save Item'));
		$this->_updateButton('delete', 'label', Mage::helper('magikfees')->__('Delete Item'));

		$this->_addButton('saveandcontinue', array(
		'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
		'onclick'   => 'saveAndContinueEdit()',
		'class'     => 'save',
		), -100);

		$this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('magikfees_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'magikfees_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'magikfees_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
	}

	public function getHeaderText()
	{
		if( Mage::registry('magikfees_data') && Mage::registry('magikfees_data')->getId() ) {
			return Mage::helper('magikfees')->__("Edit Fee '%s'", $this->htmlEscape(Mage::registry('magikfees_data')->getTitle()));
		} else {
			return Mage::helper('magikfees')->__('Add Fee');
		}
	}
}