<?php
class Iuvo_Recipe_Block_Adminhtml_Recipe_Edit_Tab_Step extends Mage_Adminhtml_Block_Abstract
{
	public function _construct() 
	{
		$this->setTemplate('recipe/recipe_steps.phtml');
	}
	
	public function getSteps()
    {
    	$id = $this->getRequest()->getParam('id');
    	$details = Mage::getModel('recipe/step')->getCollection()->addFieldToFilter('recipe_entity_id', $id);
    	return $details;
    }
}