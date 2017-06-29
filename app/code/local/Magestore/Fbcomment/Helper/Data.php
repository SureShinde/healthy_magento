<?php

class Magestore_Fbcomment_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isHiddenLike(){
		return Mage::getStoreConfig('fbcomment/general/hidden_like');
	}
	
	public function isHiddenComment(){
		return Mage::getStoreConfig('fbcomment/general/hidden_comment');
	}
	
	public function isCustomLike(){
		return Mage::getStoreConfig('fbcomment/like/custom');
	}
	
	public function isCustomComment(){
		return Mage::getStoreConfig('fbcomment/comment/custom');
	}
	
	public function getLikeCssStyle(){
		return Mage::getStoreConfig('fbcomment/like/css_style');
	}
	
	public function getCommentCssStyle(){
		return Mage::getStoreConfig('fbcomment/comment/css_style');
	}
}