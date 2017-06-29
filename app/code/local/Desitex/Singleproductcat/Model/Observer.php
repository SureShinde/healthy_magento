<?php
class Desitex_Singleproductcat_Model_Observer
{
    public function initSingleProductRedirect($observer)
    {
        $category = $observer->getEvent()->getCategory();
		$categoryId = $category->getId();
        $action = $observer->getEvent()->getControllerAction();
		if ($categoryId != 36) { // if category is not Specials....
			if (1 == $category->getProductCount()){
				$url = $category->getProductCollection()->getFirstItem()->getProductUrl();
				return $action->getResponse()->setRedirect($url);
			}
		}
    }
}
