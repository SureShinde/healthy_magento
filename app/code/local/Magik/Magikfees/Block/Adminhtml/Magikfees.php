<?php
class Magik_Magikfees_Block_Adminhtml_Magikfees extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_magikfees';
		$this->_blockGroup = 'magikfees';
		$this->_headerText = Mage::helper('magikfees')->__('All Extra Fees');
		$this->_addButtonLabel = Mage::helper('magikfees')->__('Add Fee');
		parent::__construct();
	}
}
