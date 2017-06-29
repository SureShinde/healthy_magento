<?php

class Iuvo_Recipe_Model_Recipe extends Mage_Core_Model_Abstract
{
	protected $_ingredients;
	
	protected $_steps;
	
	public function _construct()
    {    
        parent::_construct();
    	$this->_init('recipe/recipe');
    }
    
    public function getUrl() 
    {
    	if($this->getUrlKey()) {
    		$link = Mage::getBaseUrl('web') . $this->getUrlKey() . '.html';
    	} else {
    		$link = Mage::getBaseUrl('web') . 'recipe/index/index/id/' . $this->getId();
    	}
    	return $link;
    }
    
    public function getIngredients()
    {
    	if(!$this->_ingredients){
    		$this->_ingredients = Mage::getModel('recipe/ingredient')->getCollection()
    			->addFieldToFilter('recipe_entity_id', $this->getId());
    	}
    	return $this->_ingredients;
    }
    
    public function getSteps()
    {
    	if(!$this->_steps){
    		$this->_steps = Mage::getModel('recipe/step')->getCollection()
    			->addFieldToFilter('recipe_entity_id', $this->getId());
    	}
    	return $this->_steps;
    }
    
    public function getTotalRating()
    {
    	if($this->getVotesTotal() && $this->getVotes()){
    		return ceil($this->getVotesTotal() / $this->getVotes());
    	}else{
    		return 0;
    	}
    }
    
    public function isNew()
    {
    	// anything within posted within 30 days is considered new
    	return (floor((strtotime(now()) - strtotime($this->getCreatedAt())) / (60 * 60 * 24)) - 1) <= 30; 
    }
    
    protected function _beforeSave()
    {
    	if(!$this->getCreatedAt()){
    		$this->setCreatedAt(now());
    	}
    	return parent::_beforeSave();
    }
    
}