<?php
class Magik_Magikfees_Adminhtml_Magikfees_MagikfeeshelpController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() 
	{
		$this->loadLayout()
		->_setActiveMenu('magikfees/items')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		return $this;
	}

	public function indexAction() 
	{
		/*
		* LOAD THE PAGE LAYOUT
		*/
		$this->_initAction();
		
		$block = $this->getLayout()->createBlock(
                    'Mage_Core_Block_Template',
                    'magikfeestype_block',
                    array('template' => 'magikfees/helpfaq.phtml')
                );
                $this->getLayout()->getBlock('content')->append($block);
		$this->renderLayout();
	}
	
	
}
