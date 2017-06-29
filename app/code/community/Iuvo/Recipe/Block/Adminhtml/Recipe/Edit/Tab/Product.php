<?php

class Iuvo_Recipe_Block_Adminhtml_Recipe_Edit_Tab_Product extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
	  parent::__construct();
	  $this->setId('items');
	  $this->setDefaultSort('id');
	  $this->setDefaultDir('ASC');
	  $this->setSaveParametersInSession(true);
	  $this->setUseAjax(true);
	}
	
	protected function _prepareCollection()
	{
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('price');
        $this->setCollection($collection);

        return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
        $this->addColumn('skus', array(
            'header_css_class' => 'a-center',
            'type'      => 'checkbox',
            'name'      => 'skus',

            'align'     => 'center',
            'index'     => 'sku',
            'use_index' => true,
        ));

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('sales')->__('ID'),
            'sortable'  => true,
            'width'     => '60px',
            'index'     => 'entity_id'
        ));

        $this->addColumn('type', array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $this->addColumn('chooser_sku', array(
            'header'    => Mage::helper('sales')->__('SKU'),
            'name'      => 'chooser_sku',
            'width'     => '80px',
            'index'     => 'sku'
        ));
        $this->addColumn('chooser_name', array(
            'header'    => Mage::helper('sales')->__('Product Name'),
            'name'      => 'chooser_name',
            'index'     => 'name'
        ));
	  
	  return parent::_prepareColumns();
	}
    public function getGridUrl()
    {
        return $this->getUrl('*/*/edit', array('_current'=>true));
    }

    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected', array());

        return $products;
    }

}