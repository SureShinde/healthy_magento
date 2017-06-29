<?php
/**
* osCommerce Import
*
* @category   Tweakmag
* @package    Ezmage_OscommerceImport
* @copyright  Copyright (c) 2012 ezMage (http://www.ezmage.com  http://www.atwoodz.com http://www.ezosc.com )
* @author     luis villarino luis@atwoodz.com
* @license    Opensource
* @link       http://www.ezmage.com 
*/

class Ezmage_OscommerceImport_Model_Source_GetAttributeSets
{
  public function toOptionArray()
  {		
		$entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
		$attributeSetCollection = Mage::getModel('eav/entity_attribute_set')->getCollection()->setEntityTypeFilter($entityTypeId);
		foreach($attributeSetCollection as $_attributeSet){
			$_attributeSet_info = $_attributeSet->getData();
			$_attributeSet_array[] = array('value' => $_attributeSet_info['attribute_set_id'], 'label' =>$_attributeSet_info['attribute_set_name']);
		}

    return $_attributeSet_array;		
  }
}