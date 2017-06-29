<?php

class Queldorei_ShopperSettings_Block_Product_List extends Mage_Catalog_Block_Product_List
{
	public function getLoadedProductCollection()
	{
		$collection = Mage::getModel('catalog/category')->load(22)
		                  ->getProductCollection();
		$collection->getSelect()->order(array('cat_index_position ASC',
			                                'product_id'));
		return $collection;
	}
    protected function _beforeToHtml()
    {
        $collection = $this->_getProductCollection();
        $numProducts = $this->getNumProducts();
        if ( $numProducts ) {
            $collection->setPageSize($numProducts)->load();
        }

        $this->setCollection($collection);

        return parent::_beforeToHtml();
    }
    public function getBlockTitle()
    {
        $title = $this->getTitle();
        if (empty($title)) {
            $title = 'Featured Products';
        }
        return $title;
    }
}