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
        return 'sales/order_grid_collection';
    }
	
	

    protected function _prepareCollection()
    {
		$status = explode(',', Mage::getStoreConfig('trackingimport/trackinggrid/showstatus'));

		$collection = Mage::getResourceModel($this->_getCollectionClass());
		
		$collection->getSelect()->joinLeft(array('sfo'=>'sales_flat_order'),'sfo.entity_id=main_table.entity_id',array('sfo.shipping_description'));
		
		$collection->addAttributeToFilter('sfo.status', array('in' => $status));
					
		$collection->getSelect()->joinInner(array('sfoa'=>'sales_flat_order_address'),'main_table.entity_id=sfoa.parent_id',array(
          'postcode' => new Zend_Db_Expr('group_concat(sfoa.postcode SEPARATOR "###")')))->group(array('main_table.entity_id', 'sfoa.parent_id'));	
		
		$collection->getSelect()->joinLeft(array('sfoas'=>'sales_flat_order_address'),'main_table.entity_id = sfoas.parent_id AND sfoas.address_type="shipping"', array('sfoas.company'));
		
		$collection->getSelect()->columns(new Zend_Db_Expr("IFNULL(CONCAT(sfoas.company, ' ', shipping_name), shipping_name) as fullname"));
		
		$this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    
    protected function _prepareColumns()
    {
		
		$this->addColumn('increment_id', array(
            'header'=> Mage::helper('trackingimport')->__('Order #'),
            'index' => 'increment_id',
			'filter_index' => 'main_table.increment_id',
        ));
        
        $this->addColumn('created_at', array(
            'header' => Mage::helper('trackingimport')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
			'filter_index' => 'main_table.created_at',
        ));
        
        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('trackingimport')->__('Shipping Name'),
            'index' => 'fullname',
			'filter_condition_callback' => array($this, '_addCompanyNameToFilter'),
            'sortable'  => false
        ));
	
		$this->addColumnAfter('postcode', array(
            'header' => Mage::helper('sales')->__('Postcode'),
			'index' => 'postcode',
			'renderer' =>'Nick_Trackingimport_Block_Grid_Widget_Grid_Column_Filter_Postcode',
			'filter_index' => 'sfoa.postcode',
        ), 'billing_name');	
        
        $this->addColumn('shipping_method', array(
            'header' => Mage::helper('trackingimport')->__('Shipping method'),
            'index' => 'shipping_description',
			'filter_index' => 'sfo.shipping_description',
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
		
		if(Mage::getStoreConfig('trackingimport/trackinggrid/invoices')){
			$this->addColumn('generated_items', array(
				'header' => Mage::helper('trackingimport')->__('Invoices'),
				'renderer'    => 'Nick_Trackingimport_Block_Grid_Widget_Grid_Column_Renderer_GeneratedItems',
				'sortable'  => false,
				'filter' => false
			));
		}
        $this->addColumn('status', array(
            'header' => Mage::helper('trackingimport')->__('Status'),
            'index' => 'status',
			'filter_index' => 'sfo.status',
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