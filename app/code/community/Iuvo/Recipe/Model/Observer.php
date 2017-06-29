<?php
	class Iuvo_Recipe_Model_Observer
	{
		/**
		 * Adds all recipe's URLs to the rewrites.
		 * @param   Varien_Event_Observer $observer
		 * @return  Xyz_Catalog_Model_Price_Observer
		 */
		public function addUrlRewrites(Varien_Event_Observer $observer)
		{
			$store_id = Mage::app()->getStore()->getId();

			$recipeTypeCollection = Mage::getModel('recipe/type')->getCollection();
			foreach($recipeTypeCollection as $dishKey => $recipeType)
			{
				$dishtype = strtolower(str_replace(' ', '-', str_replace('HMR&reg; ', '', str_replace(' Recipes', '', $recipeType->getData('dishtype')))));
				$rewrite = Mage::getModel('core/url_rewrite')->getCollection()
					->addFieldToFilter('request_path', 'hmr-recipes/'.$dishtype)
					->getFirstItem();
				if($rewrite->getId()) {
					$rewrite->setStoreId($store_id)
						->setIsSystem(1)
						->setIdPath('dishtype/'.$dishKey)
						->setTargetPath('recipe/index/list/dish/'.$dishKey)
						->setRequestPath('hmr-recipes/'.$dishtype)
						->save();
				} else {
					Mage::getModel('core/url_rewrite')
						->setIsSystem(1)
						->setStoreId($store_id)
						->setIdPath('dishtype/'.$dishKey)
						->setTargetPath('recipe/index/list/dish/'.$dishKey)
						->setRequestPath('hmr-recipes/'.$dishtype)
						->save();
				}
			}

			$recipeCollection = Mage::getModel('recipe/recipe')->getCollection();
			foreach($recipeCollection as $recipe)
			{
				$model = Mage::getModel('recipe/recipe')->load($recipe->getData('recipe_id'));
				$rewrite = Mage::getModel('core/url_rewrite')->getCollection()
					->addFieldToFilter('target_path', 'recipe/index/index/id/'.$model->getId())
					->getFirstItem();
				if($rewrite->getId()) {
					$rewrite->setStoreId($store_id)
						->setIsSystem(1)
						->setIdPath('id/'.$model->getId())
						->setTargetPath('recipe/index/index/id/'.$model->getId())
						->setRequestPath($model->getData('url_key').'.html')
						->save();
				} else {
					Mage::getModel('core/url_rewrite')
						->setIsSystem(1)
						->setStoreId($store_id)
						->setIdPath('id/'.$model->getId())
						->setTargetPath('recipe/index/index/id/'.$model->getId())
						->setRequestPath($model->getData('url_key').'.html')
						->save();
				}
			}
		}
	}