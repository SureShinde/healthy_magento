<?php
/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2014 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */
class Magebird_Popup_DatasharingController extends Mage_Core_Controller_Front_Action
{
  	public function statisticsAction() {     
      //allow data sharing?      
      $allow = Mage::getStoreConfig('popup/statistics/data_sharing');      
      $sharingdataKey = Mage::getStoreConfig('popup/general/sharingdata_key');
      //only magebird knows this key, no others are allowed to view data
      $requestedKey = $this->getRequest()->getParam('key');
      if($allow && $sharingdataKey==$requestedKey){
        //get only popup data
        $collection = Mage::getModel('popup/popup')->getCollection()->toArray();
        $json = json_encode($collection['items']);
        $this->getResponse()->setBody($json);
      }      
  	}     
} 