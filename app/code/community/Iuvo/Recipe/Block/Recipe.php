<?php
class Iuvo_Recipe_Block_Recipe extends Mage_Catalog_Block_Product_Abstract
{	
	protected $_recipe;
	
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    public function getRecipe()
    {
    	$id = $this->getRequest()->getParam('id');
		return Mage::getModel('recipe/recipe')->getCollection()->addFieldToFilter('recipe_id', $id)->getFirstItem();
    }
    
        
    public function getRecipes($col = 'recipe_id', $dir = 'DESC')
    {
    	$recipes = Mage::getModel('recipe/recipe')->getCollection();
    		//->addFieldToFilter('status', 2);
    	if($this->getRequest()->getParam('dish') != '') {
    		$recipes->addFieldToFilter('dishtype', $this->getRequest()->getParam('dish'));
    	}
    	if($this->getRequest()->getParam('diff') != '') {
    		$recipes->addFieldToFilter('difficulty', $this->getRequest()->getParam('diff'));
    	}
    	$recipes->addFieldToFilter('status', 2);
    	$recipes->setOrder($col, $dir);
    	return $recipes;
    }
    
    public function getReviews()
    {
    	$recipe = $this->getRecipe();
    	return Mage::getModel('recipe/review')->getCollection()
    		->addFieldToFilter('approved', 1)
    		->addFieldToFilter('recipe_id', $recipe->getId());
    }
    
    public function getRecipeProducts($_skus)
    {
    	$skus = explode(",", $_skus);
    	$products = array();
    	foreach($skus as $sku) {
    		$products[] = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
    	}
    	return $products;
    }
        
    public function getDifficultyVal($id)
    {
		switch($id) {
			case '1':
				return 'Easy';
				break;
			case '2':
				return 'Moderate';
				break;
			case '3':
				return 'Hard';
				break;
		}
    }
    
    public function getIngredients($id) 
    {
    	return Mage::getModel('recipe/ingredient')->getCollection()
    		->addFieldToFilter('recipe_entity_id', $id);
    }
    
    public function getSteps($id)
    {
    	$col = Mage::getModel('recipe/step')->getCollection()
    		->addFieldToFilter('recipe_entity_id', $id);
    	$col->getSelect()->order('main_table.order ASC');
    	return $col;
    }
    
}