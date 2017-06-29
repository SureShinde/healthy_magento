<?php
class Inchoo_EnhancedExport_Block_Mage_Adminhtml_Sales_Order_Grid extends MageWorx_Adminhtml_Block_Orderspro_Sales_Order_Grid //Mage_Adminhtml_Block_Sales_Order_Grid
{
    /**
     * Rows per page for import
     *
     * @var int
     */
    protected $_exportPageSize = 500;
    public function __construct()
    {
        parent::__construct();
    }
	 
	
    protected function _prepareColumns()
    {
       /*$this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
			'filter_index'=>'main_table.increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '200px',
			'filter_index'=>'main_table.created_at',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
			'filter_index'=>'main_table.billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
			'filter_index'=>'main_table.shipping_name',
        ));
		
		$this->addColumn('shipping_method', array(
			'header' => Mage::helper('sales')->__('Shipping Method'),
			'index' => 'shipping_description',
			'filter_index'=>'sales_flat_order.shipping_method',
		));
		
		$this->addColumn('region', array(
			'header' => Mage::helper('sales')->__('Shipping Region'),
			'index' => 'region',
			'filter_index'=>'sales_flat_order_address.region',
			
		));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
			'filter_index'=>'main_table.base_grand_total',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
			'filter_index'=>'main_table.grand_total',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
			'filter_index'=>'main_table.status',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));*/

		parent::_prepareColumns();

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'*/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
			
		// adding pieces needed for the shipping export	
		$this->addColumn('street', array(
            'header' => Mage::helper('sales')->__('Street'),
            'index' => 'street',
            'filter_index'=>'sales_flat_order_address.street',
        ));

        $this->addColumn('weight', array(
            'header' => Mage::helper('sales')->__('Weight'),
            'index' => 'weight',
            'filter_index'=>'sales_flat_order.weight',
        ));

       	$this->addColumn('city', array(
            'header' => Mage::helper('sales')->__('City'),
            'index' => 'city',
            'filter_index'=>'sales_flat_order_address.city',
        ));

       $this->addColumn('postcode', array(
            'header' => Mage::helper('sales')->__('Postcode'),
            'index' => 'postcode',
            'filter_index'=>'sales_flat_order_address.postcode',
        ));

       $this->addColumn('country_id', array(
            'header' => Mage::helper('sales')->__('Country'),
            'index' => 'country_id',
            'filter_index'=>'sales_flat_order_address.country_id',
        ));

       
        }
		
		$this->addExportType('*/*/exportCsvEnhanced', Mage::helper('sales')->__('FedEx Export'));
        
    }
    public function getCsvFileEnhanced()
    {
        $this->_isExport = true;
        $this->_prepareGrid();
        $io = new Varien_Io_File();
        $path = Mage::getBaseDir('var') . DS . 'export' . DS; //best would be to add exported path through config
        $name = md5(microtime());
        $file = $path . DS . $name . '.csv';
        /**
         * It is possible that you have name collision (summer/winter time +1/-1)
         * Try to create unique name for exported .csv file
         */
        while (file_exists($file)) {
            sleep(1);
            $name = md5(microtime());
            $file = $path . DS . $name . '.csv';
        }
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);
        $io->streamWriteCsv($this->_getExportHeaders());
        //$this->_exportPageSize = load data from config
        $this->_exportIterateCollectionEnhanced('_exportCsvItem', array($io));
        if ($this->getCountTotals()) {
            $io->streamWriteCsv($this->_getExportTotals());
        }
        $io->streamUnlock();
        $io->streamClose();
        return array(
            'type'  => 'filename',
            'value' => $file,
            'rm'    => false // can delete file after use
        );
    }
    public function _exportIterateCollectionEnhanced($callback, array $args)
    {
        $originalCollection = $this->getCollection();
        $count = null;
        $page  = 1;
        $lPage = null;
        $break = false;
        $ourLastPage = 10;
		
		 while ($break !== true) {
            $collection = clone $originalCollection;
            $collection->setPageSize($this->_exportPageSize);
            $collection->setCurPage($page);
            $collection->load(/*true, true*/);
            if (is_null($count)) {
                $count = $collection->getSize();
                $lPage = $collection->getLastPageNumber();
            }
            if ($lPage == $page || $ourLastPage == $page) {
                $break = true;
            }
            $page ++;
            foreach ($collection as $item) {
/*				echo "help".$item->getState();
				echo "<pre>";
		print_r($item);
		echo "</pre>";
		exit();*/
              	if($item->getStatus() == 'processing'){
					unset($item->created_at);
	                call_user_func_array(array($this, $callback), array_merge(array($item), $args));
					$item->setState($item->getState(), 'shp_pnd'); 
					//$item->save();
				}
				
            }
        }
        //exit();
    }
}