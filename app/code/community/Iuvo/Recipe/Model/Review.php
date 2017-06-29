<?php

class Iuvo_Recipe_Model_Review extends Mage_Core_Model_Abstract
{
	public function _construct()
    {    
        parent::_construct();
    	$this->_init('recipe/review');
    }
    
    public function addReview($data)
    {
    	$this->setId(null)
    		->setRecipeId($data['recipe_id'])
    		->setReviewName($data['name'])
    		->setReviewText($data['text'])
    		->setReviewEmail($data['email'])
    		->setCreatedAt(now())
    		->save();
    }  
}