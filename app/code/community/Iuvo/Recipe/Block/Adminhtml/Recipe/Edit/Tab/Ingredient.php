<?php
class Iuvo_Recipe_Block_Adminhtml_Recipe_Edit_Tab_Ingredient extends Mage_Adminhtml_Block_Abstract
{
	public function _construct() 
	{
		$this->setTemplate('recipe/recipe_ingredients.phtml');
	}
	
	public function getIngredients()
    {
    	$id = $this->getRequest()->getParam('id');
    	$details = Mage::getModel('recipe/ingredient')->getCollection()->addFieldToFilter('recipe_entity_id', $id);
    	return $details;
    }
}
