<?php
class NeXtIT_FastSelectAdd_Helper_Data extends Mage_Core_Helper_Abstract
{	
	/**
     * Generate menu HTML structure for left navigation
     *
     * @param int $parentId
     * @param bool $isChild
     * @return string $html
     */
	public function getTreeCategoriesForFastSelect($parentId, $isChild)
	{		
	    $allCats = Mage::getModel('catalog/category')->getCollection()
	                ->addAttributeToSelect('*')
	                ->addAttributeToFilter('is_active','1')
	                ->addAttributeToFilter('include_in_menu','1')
	                ->addAttributeToFilter('parent_id', array('eq' => $parentId))
	                ->addAttributeToSort('position', 'asc');
	                
	    $class = ($isChild) ? "sub-cat-list" : "cat-list";
	    $html = '<ul class="'.$class.'">';
	    
	    $excludedCategoriesIds = array('14', '26', '23', '5', '31');

	    foreach($allCats as $category)
	    {
		    if (!in_array($category->getId(), $excludedCategoriesIds))
		    {
		        $html .= '<li><a href="#cat'.$category->getId().'_anchor">'.$category->getName().'</a></li>';
		        $subcats = $category->getChildren();
		        if($subcats != ''){
		            $html .= self::getTreeCategoriesForFastSelect($category->getId(), true);
		        }
		        $html .= '</li>'; 
		    }
	    }
	    $html .= '</ul>';
	    return $html;
	}
	
	public function getCategoriesForFastSelect($parentId, $isChild)
	{
		$allCats = Mage::getModel('catalog/category')->getCollection()
	                ->addAttributeToSelect('*')
	                ->addAttributeToFilter('is_active','1')
	                ->addAttributeToFilter('include_in_menu','1')
	                ->addAttributeToFilter('parent_id', array('eq' => $parentId))
	                ->addAttributeToSort('position', 'asc');
	    
	    $excludedCategoriesIds = array('14', '26', '23', '5', '31');
	    
	    $fastSelectCategories = array();
	    foreach($allCats as $category)
	    {
		    if (!in_array($category->getId(), $excludedCategoriesIds))
		    {
				$fastSelectCategories[] = $category->getId();
			}
		}
		
		return $fastSelectCategories;
	}
		
}