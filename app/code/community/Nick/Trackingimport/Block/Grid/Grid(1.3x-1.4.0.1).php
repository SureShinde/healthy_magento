<?php

class Nick_Trackingimport_Block_Grid_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	protected $_parentTemplate = '';
	
    public function __construct()
    {
        parent::__construct();
        $this->setId('importgrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText(Mage::helper('customer')->__('No Items Found'));
		$this->setSaveParametersInSession(true);
    }
	
	protected function _addCompanyNameToFilter($collection, $column){
          $condition = $column->getFilter()->getCondition();
		  $filter = $condition['like'];
          $collection->getSelect()->where("shipping_name LIKE $filter OR sfoas.company LIKE $filter");
          return $this;
      }

	
    protected function _getCollectionClass()
    {
        return 'sales/order_collection';
    }
	
	

    protected function _prepareCollection()
    {
    	 $collection = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('*')
            ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
			->joinAttribute('company', 'order_address/company', 'shipping_address_id', null, 'left')
			->joinAttribute('postcode', 'order_address/postcode', 'shipping_address_id', null, 'left')
			->addExpressionAttributeToSelect('shipping_name',
                "IFNULL(CONCAT({{company}}, ' ', {{shipping_firstname}}, ' ', {{shipping_lastname}}), CONCAT({{shipping_firstname}}, ' ', {{shipping_lastname}}))",
                array('shipping_firstname', 'shipping_lastname', 'company'));
        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
    
    
    protected function _prepareColumns()
    {
		
		$this->addColumn('increment_id', array(
            'header'=> Mage::helper('trackingimport')->__('Order #'),
            'index' => 'increment_id',
        ));
        
        $this->addColumn('created_at', array(
            'header' => Mage::helper('trackingimport')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));
        
        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('trackingimport')->__('Shipping Name'),
            'index' => 'shipping_name',
            'sortable'  => false
        ));
	
		$this->addColumnAfter('postcode', array(
            'header' => Mage::helper('sales')->__('Postcode'),
			'index' => 'postcode',
			'renderer' =>'Nick_Trackingimport_Block_Grid_Widget_Grid_Column_Filter_Postcode',
        ), 'billing_name');	
        
        $this->addColumn('shipping_method', array(
            'header' => Mage::helper('trackingimport')->__('Shipping method'),
            'index' => 'shipping_description',
		//	'filter'    => 'Nick_Trackingimport_Block_Grid_Widget_Grid_Column_Filter_ShippingMethod',
		//	'type'  => 'options',
            'sortable'  => false,
        ));
       

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('trackingimport')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
            'filter'    => false,
            'sortable'  => false
        ));
		
		$this->addColumn('trackinginfo', array(
            'header' => Mage::helper('trackingimport')->__('Tracking Info'),
            'renderer'    => 'Nick_Trackingimport_Block_Grid_Widget_Grid_Column_Renderer_Tracking',
            'sortable'  => false,
            'filter' => false
        ));
		
        $this->addColumn('status', array(
            'header' => Mage::helper('trackingimport')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses()
        ));


        
        return parent::_prepareColumns();
    }
	
	 public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('adminhtml/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

    protected function _prepareMassaction()
    {
		
		$this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
		
	/*	$this->getMassactionBlock()->addItem('dispatch', array(
                'label' => 'Mark As Dispatched',
                'url' => Mage::app()->getStore()->getUrl('trackingimport/grid/dispatchorder'),
            ));
		
		$this->getMassactionBlock()->addItem('printlabel', array(
                'label' => 'Print Shipping Label',
                'url' => Mage::app()->getStore()->getUrl('orderexport/export_order/shippinglabelexport'),
            ));
	   
        return $this;*/
    }

}