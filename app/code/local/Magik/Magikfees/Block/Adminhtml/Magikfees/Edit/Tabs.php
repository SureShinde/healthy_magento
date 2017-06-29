<?php

class Magik_Magikfees_Block_Adminhtml_Magikfees_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('magikfees_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('magikfees')->__('Magik Extra Fee'));
	}

	protected function _beforeToHtml()
	{
		$this->addTab('form_section', array(
		'label'     => Mage::helper('magikfees')->__('Create Extra Fee'),
		'title'     => Mage::helper('magikfees')->__('Create Extra Fee'),
		'content'   => $this->getLayout()->createBlock('magikfees/adminhtml_magikfees_edit_tab_form')->toHtml(),
		));

		return parent::_beforeToHtml();
	}
}