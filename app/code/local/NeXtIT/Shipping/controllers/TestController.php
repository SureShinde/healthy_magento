<?php
/**
 * Created by PhpStorm.
 * User: mbitson
 * Date: 4/20/2015
 * Time: 8:45 AM
 */
class NeXtIT_Shipping_TestController extends Mage_Core_Controller_Front_Action
{
	public function cronAction()
	{
		$observerModel = Mage::getModel('nextit_shipping/observer');
		$observerModel->processShipmentEmails();
	}
}