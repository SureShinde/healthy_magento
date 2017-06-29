<?php
class Iuvo_Recipe_Block_Adminhtml_Recipe_Edit_Tab_Review extends Mage_Adminhtml_Block_Abstract
{
	public function _construct() 
	{
		$this->setTemplate('recipe/recipe_reviews.phtml');
	}
	
	public function getReviews()
    {
    	$id = $this->getRequest()->getParam('id');
    	$details = Mage::getModel('recipe/review')->getCollection()->addFieldToFilter('recipe_id', $id);
    	return $details;
    }
}