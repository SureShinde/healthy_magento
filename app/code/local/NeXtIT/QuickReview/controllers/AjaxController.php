<?php
class NeXtIT_QuickReview_AjaxController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$this->loadLayout(false);
		$this->renderLayout();
	}
}
?>