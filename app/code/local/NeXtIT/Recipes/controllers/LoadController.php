<?php
class NeXtIT_Recipes_LoadController extends Mage_Core_Controller_Front_Action {
	public function IndexAction()
	{
		$types   = $this->prepareTypes();
		$recipes = $this->prepareRecipes($types);
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody(json_encode($recipes));
	}


	/**
	 * @return mixed
	 */
	private function prepareTypes()
	{
		$types   = Mage::getModel('recipe/type')->getCollection()->getData();
		$typeRef = array();
		foreach ($types as $type)
		{
			$typeRef[$type['type_id']] = $type['dishtype'];
		}
		return $typeRef;
	}


	/**
	 * @param $types
	 *
	 * @return mixed
	 */
	private function prepareRecipes($types)
	{
		$recipes = Mage::getModel('recipe/recipe')->getCollection();
		$recipesReturn = array();
		foreach ($recipes as $key => $recipe)
		{
			if(isset( $types[$recipe->getDishtype()])){
				$recipe->setDishtype($types[$recipe->getDishtype()]);
				$recipesReturn[$key]                = $recipe->getData();
				$recipesReturn[$key]['steps']       = $recipe->getSteps()->getData();
				$recipesReturn[$key]['ingredients'] = $recipe->getIngredients()->getData();
				$recipesReturn[$key]['url']         = $recipe->getUrl();
			}
		}
		return $recipesReturn;
	}
}