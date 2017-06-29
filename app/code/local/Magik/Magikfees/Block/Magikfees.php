<?php
class Magik_Magikfees_Block_Magikfees extends Mage_Core_Block_Template
{
	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}
	public function getMagikfees()
	{
		if (!$this->hasData('magikfees')) {
			$this->setData('magikfees', Mage::registry('magikfees'));
		}
		return $this->getData('magikfees');
	}
}