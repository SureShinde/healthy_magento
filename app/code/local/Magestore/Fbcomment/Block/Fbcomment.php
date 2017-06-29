<?php

class Magestore_Fbcomment_Block_Fbcomment extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    public function getUniqueId(){
		return Mage::getStoreConfig('fbcomment/general/unique_id_of_site');
	}
	
	public function numberOfCommment(){
 		return Mage::getStoreConfig('fbcomment/general/show_number_of_comment');
	}
	
	public function widthOfCommmentBox(){
 		return Mage::getStoreConfig('fbcomment/general/width_of_comment_box');
	}
	
	public function publicFeed(){
		return Mage::getStoreConfig('fbcomment/general/publish_feed');
	}
	
	public function getXid(){
		$productId = $this->getRequest()->getParam('id');
		return Mage::getModel('catalog/product')->load($productId)->getId();
	}
	
	public function getLanguage(){
		return Mage::getStoreConfig('fbcomment/general/language');
	}
	
	public function isRoundedBox(){
		return Mage::getStoreConfig('fbcomment/general/rounded_box');
	}
	
	public  function isReserveOrdering(){
		return Mage::getStoreConfig('fbcomment/general/reverse_ordering');
	}
	
	public function getCssUrl(){
		$fbcomment = Mage::getModel('fbcomment/fbcomment')->getCollection()
					->setOrder('fbcomment_id', 'DESC')
					->getFirstItem();
		return $this->getUrl('fbcomment/index/getCss'). '?' . $fbcomment->getId();
	}
	
	public function getTemplate(){
		if(!Mage::helper('magenotification')->checkLicenseKey('Fbcomment')){
			return null;
		}
		return parent::getTemplate();
	}
	public function getProduct() {
		return Mage::registry('current_product');
	}	
	
	public function getUrlForntEnd(){
		return Mage::getBaseUrl();
	}
}