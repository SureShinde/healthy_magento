<?php
class Nick_Trackingimport_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getAllowedShippingMethods($addEmpty = false) {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();

		$options = array();
		$options[] = array('value' => '', 'label' => '');
	
		foreach($methods as $_ccode => $_carrier)
		{
			$_methodOptions = array();
			if($_methods = $_carrier->getAllowedMethods())
			{
				foreach($_methods as $_mcode => $_method)
				{
					$_code = $_ccode . '_' . $_mcode;
					$_methodOptions[] = array('value' => $_code, 'label' => $_method);
				}
	
				if(!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
					$_title = $_ccode;
	
				$options[] = array('value' => $_methodOptions, 'label' => $_title);
			}
		}
	
		return $options;
    }
}