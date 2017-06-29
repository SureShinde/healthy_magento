<?php

class Magestore_Fbcomment_Model_Observer {

	public function saveFbcommentConfig($observer)
	{
		$fbcomment = Mage::getModel('fbcomment/fbcomment');
		$fbcomment->setEditTime(now());
		try{
			$fbcomment->save();
		}catch(Exception $e){
			Mage::getSingleton('core/session')->addError($e);
		}
	}
	
	public function controller_action_predispatch_adminhtml($observer)
	{
		$controller = $observer->getControllerAction();
		if($controller->getRequest()->getControllerName() != 'system_config'
			|| $controller->getRequest()->getActionName() != 'edit')
			return;
		$section = $controller->getRequest()->getParam('section');
		if($section != 'fbcomment')
			return;
		$magenotificationHelper = Mage::helper('magenotification');
		if(!$magenotificationHelper->checkLicenseKey('Fbcomment')){
			$message = $magenotificationHelper->getInvalidKeyNotice();
			echo $message;die();
		}elseif((int)$magenotificationHelper->getCookieLicenseType() == Magestore_Magenotification_Model_Keygen::TRIAL_VERSION){
			Mage::getSingleton('adminhtml/session')->addNotice($magenotificationHelper->__('You are using a trial version of Facebook Comment extension. It will be expired on %s.',
														 $magenotificationHelper->getCookieData('expired_time')
											));
		}
	}			
}