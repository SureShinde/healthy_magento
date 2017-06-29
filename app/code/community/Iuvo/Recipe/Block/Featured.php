<?php
 
class Iuvo_Recipe_Block_Featured extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
    }
    
    public function getFeaturedRecipes()
    {
    	$collection = Mage::getModel('recipe/recipe')->getCollection()
    		->addFieldToFilter('featured', 1);
    	$collection->getSelect()->limit(5); 
    	return $collection;
    }
}