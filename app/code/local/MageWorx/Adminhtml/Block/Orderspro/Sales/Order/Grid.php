<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @copyright  Copyright (c) 2013 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * Orders Pro extension
 *
 * @category   MageWorx
 * @package    MageWorx_OrdersPro
 * @author     MageWorx Dev Team
 */
 
class MageWorx_Adminhtml_Block_Orderspro_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid //MageWorx_Adminhtml_Block_Orderspro_Sales_Order_Grid_Abstract
{   
    
    public function __construct() {
        parent::__construct();
        $this->setDefaultFilter(array('order_group'=>0)); // Actual
    }
    
    protected function _prepareColumns() {
        $helper = Mage::helper('orderspro');
        if (!$helper->isEnabled()) return parent::_prepareColumns();        
        
        $listColumns = $helper->getGridColumns();        
        //$currencyCode = $helper->getCurrentCurrencyCode();
        
        foreach ($listColumns as $column) {
            switch ($column) {
                
                // standard fields
                
                case 'real_order_id':
                    $this->addColumn('real_order_id', array(
                        'header'=> Mage::helper('sales')->__('Order #'),
                        'width' => '80px',
                        'type'  => 'text',
                        'index' => 'increment_id',
						'filter_index'=>'main_table.increment_id',
                    ));
                break;    

                case 'store_id':
                    if (!Mage::app()->isSingleStoreMode()) {
                        $this->addColumn('store_id', array(
                            'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                            'index'     => 'store_id',
                            'type'      => 'store',
                            'store_view'=> true,
                            'display_deleted' => true,
                        ));
                    }
                break;
                
                case 'created_at':
                    $this->addColumn('created_at', array(
                        'header' => Mage::helper('sales')->__('Purchased On'),
                        'index' => 'created_at',
                        'type' => 'datetime',
                        'width' => '150px',
						'filter_index'=>'main_table.created_at',
                    ));
                    if (method_exists($this, '_prepareAWDeliverydateColumns')) $this->_prepareAWDeliverydateColumns();
                break;   

                case 'billing_name':
                    $this->addColumn('billing_name', array(
                        'header' => Mage::helper('sales')->__('Bill to Name'),
                        'index' => 'billing_name',
                    ));
                break;    

                case 'shipping_name':
                    $this->addColumn('shipping_name', array(
                        'header' => Mage::helper('sales')->__('Ship to Name'),
                        'index' => 'shipping_name',
                    ));
                break;
				
				
				

                case 'base_grand_total':    
                    $this->addColumn('base_grand_total', array(
                        'header' => Mage::helper('sales')->__('G.T. (Base)'),
                        'index' => 'base_grand_total',
                        'type'  => 'currency',
                        'currency' => 'base_currency_code',
						'filter_index'=>'main_table.base_grand_total'
                    ));
                break;    

                    
                case 'grand_total':
                    $this->addColumn('grand_total', array(
                        'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
                        'index' => 'grand_total',
                        'type'  => 'currency',
                        'currency' => 'order_currency_code',
						'filter_index'=>'main_table.grand_total',
                    ));
                break;    
                    
                
                case 'status':
                    $this->addColumn('status', array(
                        'header' => Mage::helper('sales')->__('Status'),
                        'index' => 'status',
                        'type'  => 'options',
                        'width' => '140px',
                        'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
						'filter_index'=>'main_table.status',
                    ));
                break;    
                    

                case 'action':    
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
                    }
                break;
                
                // additional fields
                
                case 'product_names':                
                    $this->addColumn('product_names', array(            
                        'renderer'  => 'mageworx/orderspro_sales_order_grid_renderer_products',
                        'header' => $helper->__('Product Name(s)').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                        'index' => 'product_names'
                        ));
                break;
            
                case 'product_skus':                
                    $this->addColumn('product_skus', array(            
                        'renderer'  => 'mageworx/orderspro_sales_order_grid_renderer_products',
                        'header' => $helper->__('SKU(s)'),
                        'index' => 'skus'
                        ));
                break;
                
                case 'product_options':                
                    $this->addColumn('product_options', array(            
                        'renderer'  => 'mageworx/orderspro_sales_order_grid_renderer_products',
                        'header' => $helper->__('Product Option(s)'),
                        'index' => 'product_options',
                        'filter'    => false,
                        'sortable'  => false
                        ));
                break;

                case 'customer_email':
                    $this->addColumn('customer_email', array(
                        'type'  => 'text',
                        'header' => $helper->__('Customer Email'),
                        'index' => 'customer_email',
						'filter_index' => 'main_table.customer_email'
                        ));
                break;
            

                case 'customer_group':
                    $this->addColumn('customer_group', array(
                        'renderer'  => 'mageworx/orderspro_sales_order_grid_renderer_registry',
                        'type'  => 'options',
                        'options' => $helper->getCustomerGroups(),
                        'header' => $helper->__('Customer Group'),
                        'index' => 'customer_group_id',
                        'align' => 'center'
                        ));
                break;    
                    

                case 'payment_method':
                    $this->addColumn('payment_method', array(            
                        'renderer'  => 'mageworx/orderspro_sales_order_grid_renderer_registry',
                        'type'  => 'options',
                        'options' => $helper->getAllPaymentMethods(),
                        'header' => $helper->__('Payment Method'),
                        'index' => 'method',
                        'align' => 'center'
                        ));
                break;
                    

                case 'base_total_refunded':
                    $this->addColumn('base_total_refunded', array(                            
                        'type'  => 'currency',
                        'currency' => 'base_currency_code',                        
                        'header' => $helper->__('Total Refunded (Base)'),
                        'index' => 'base_total_refunded',
                        'total' => 'sum'
                        ));
                break;
                case 'total_refunded':
                    $this->addColumn('total_refunded', array(                            
                        'type'  => 'currency',
                        'currency' => 'order_currency_code',                        
                        'header' => $helper->__('Total Refunded (Purchased)'),
                        'index' => 'total_refunded',
                        'total' => 'sum'
                        ));
                break;
                    

                case 'shipping_method':
                    $this->addColumn('shipping_method', array(            
                        'renderer'  => 'mageworx/orderspro_sales_order_grid_renderer_registry',
                        'type'  => 'options',
                        'options' => $helper->getAllShippingMethods(),
                        'filter'    => 'mageworx/orderspro_sales_order_grid_filter_shipping',
                        'header' => $helper->__('Shipping Method'),
                        'index' => 'shipping_method',
                        'align' => 'left',
						'width'     => '290px',
						'filter_index'=>'sfo.shipping_method'
                        ));
                break;
            
            
                case 'shipped':
                    $this->addColumn('shipped', array(                            
                        'renderer'  => 'mageworx/orderspro_sales_order_grid_renderer_registry',                
                        'type'  => 'options',
                        'options' => $helper->getShippedStatuses(),
                        'header' => $helper->__('Shipped'),
                        'index' => 'shipped',
                        'align' => 'center'
                        ));
                break;                    
                    
                case 'order_group':
                    $this->addColumn('order_group', array(                            
                        'renderer'  => 'mageworx/orderspro_sales_order_grid_renderer_registry',                
                        'type'  => 'options',
                        'options' => $helper->getOrderGroups(),
                        'header' => $helper->__('Group'),
                        'index' => 'order_group_id',
                        'align' => 'center',                        
                        ));
                break;


                case 'qnty':
                    $this->addColumn('qnty', array(                            
                        'renderer'  => 'mageworx/orderspro_sales_order_grid_renderer_qnty',                
                        'filter'    => false,
                        'sortable'  => false,                
                        'header' => $helper->__('Qnty'),
                        'index' => 'total_qty',                
                        ));
                break;
                
                case 'weight':
                    $this->addColumn('weight', array(
                        'type'    => 'number',
                        'header' => $helper->__('Weight'),
                        'index' => 'weight',                
                        ));
                break;
            
                case 'base_tax_amount':
                    $this->addColumn('base_tax_amount', array(                            
                        'type'  => 'currency',
                        'currency' => 'base_currency_code',
                        'header' => $helper->__('Tax Amount (Base)'),
                        'index' => 'base_tax_amount'
                        ));
                break;
                case 'tax_amount':
                    $this->addColumn('tax_amount', array(                            
                        'type'  => 'currency',
                        'currency' => 'order_currency_code',
                        'header' => $helper->__('Tax Amount (Purchased)'),
                        'index' => 'tax_amount'
                        ));
                break; 
                    
                case 'base_discount_amount':
                    $this->addColumn('base_discount_amount', array(                            
                        'type'  => 'currency',
                        'currency' => 'base_currency_code',
                        'header' => $helper->__('Discount (Base)'),
                        'index' => 'base_discount_amount'                
                        ));
                break;    
                case 'discount_amount':
                    $this->addColumn('discount_amount', array(                            
                        'type'  => 'currency',
                        'currency' => 'order_currency_code',
                        'header' => $helper->__('Discount (Purchased)'),
                        'index' => 'discount_amount'                
                        ));
                break;
            
                case 'base_internal_credit':
                    if (Mage::getConfig()->getModuleConfig('MageWorx_CustomerCredit')->is('active', true)) {
                        $this->addColumn('base_internal_credit', array(
                            'type'  => 'currency',
                            'currency' => 'base_currency_code',
                            'header' => $helper->__('Internal Credit (Base)'),
                            'index' => 'base_customer_credit_amount'
                            ));
                    }
                break;
                case 'internal_credit':
                    if (Mage::getConfig()->getModuleConfig('MageWorx_CustomerCredit')->is('active', true)) {
                        $this->addColumn('internal_credit', array(
                            'type'  => 'currency',
                            'currency' => 'order_currency_code',
                            'header' => $helper->__('Internal Credit (Purchased)'),
                            'index' => 'customer_credit_amount'
                            ));
                    }
                break;
                
                case 'billing_company':
                    $this->addColumn('billing_company', array(                            
                        'type'  => 'text',
                        'header' => $helper->__('Bill to Company'),
                        'index' => 'billing_company',
                        'align' => 'center'
                        ));
                break;
            
                case 'shipping_company':
                    $this->addColumn('shipping_company', array(
                        'type'  => 'text',
                        'header' => $helper->__('Ship to Company'),
                        'index' => 'shipping_company',
                        'align' => 'center'
                        ));
                break;
            
                case 'billing_street':
                    $this->addColumn('billing_street', array(                            
                        'type'  => 'text',
                        'renderer'  => 'mageworx/orderspro_sales_order_grid_renderer_street',
                        'header' => $helper->__('Bill to Street'),
                        'index' => 'billing_street',
                        'align' => 'center'
                        ));
                break;            
                case 'shipping_street':
                    $this->addColumn('shipping_street', array(
                        'type'  => 'text',
                        'renderer'  => 'mageworx/orderspro_sales_order_grid_renderer_street',
                        'header' => $helper->__('Ship to Street'),
                        'index' => 'shipping_street',
                        'align' => 'center'
                        ));
                break;
            
            
                case 'billing_city':
                    $this->addColumn('billing_city', array(                            
                        'type'  => 'text',
                        'header' => $helper->__('Bill to City'),
                        'index' => 'billing_city',
                        'align' => 'center'
                        ));
                break;            
                case 'shipping_city':
                    $this->addColumn('shipping_city', array(
                        'type'  => 'text',
                        'header' => $helper->__('Ship to City'),
                        'index' => 'shipping_city',
                        'align' => 'center'
                        ));
                break;
            
                case 'billing_region':
                    $this->addColumn('billing_region', array(                            
                        'type'  => 'text',
                        'header' => $helper->__('Bill to State'),
                        'index' => 'billing_region',
                        'align' => 'center'
                        ));
                break;            
                case 'shipping_region':
                    $this->addColumn('shipping_region', array(
                        'type'  => 'text',
                        'header' => $helper->__('Ship to State'),
                        'index' => 'shipping_region',
                        'align' => 'center',
                        'width' => '120px',
						'filter_index'=>'main_table.grand_total'
                        ));
                break;
            
                case 'billing_country':
                    $this->addColumn('billing_country', array(                            
                        'type'  => 'options',
                        'options' => $this->getCountryNames(),
                        'header' => $helper->__('Bill to Country'),
                        'index' => 'billing_country_id',
                        'align' => 'center'
                        ));
                break;
                case 'shipping_country':
                    $this->addColumn('shipping_country', array(
                        'type'  => 'options',
                        'header' => $helper->__('Ship to Country'),
                        'options' => $this->getCountryNames(),
                        'index' => 'shipping_country_id',
                        'align' => 'center'
                        ));
                break;
            
            
                case 'billing_postcode':
                    $this->addColumn('billing_postcode', array(                            
                        'type'  => 'text',
                        'header' => $helper->__('Billing Postcode'),
                        'index' => 'billing_postcode',
                        'align' => 'center'
                        ));
                break;
            
                case 'shipping_postcode':
                    $this->addColumn('shipping_postcode', array(
                        'type'  => 'text',
                        'header' => $helper->__('Shipping Postcode'),
                        'index' => 'shipping_postcode',
                        'align' => 'center'
                        ));
                break;
            
                case 'billing_telephone':
                    $this->addColumn('billing_telephone', array(                            
                        'type'  => 'text',
                        'header' => $helper->__('Billing Telephone'),
                        'index' => 'billing_telephone',
                        'align' => 'center'
                        ));
                break;
            
                case 'shipping_telephone':
                    $this->addColumn('shipping_telephone', array(
                        'type'  => 'text',
                        'header' => $helper->__('Shipping Telephone'),
                        'index' => 'shipping_telephone',
                        'align' => 'center'
                        ));
                break;
            
                case 'coupon_code':
                    $this->addColumn('coupon_code', array(
                        'type'  => 'text',
                        'header' => $helper->__('Coupon Code'),
                        'align' => 'center',
                        'index' => 'coupon_code'       
                        ));
                break;
            
                case 'is_edited':
                    $this->addColumn('is_edited', array(                            
                        'renderer'  => 'mageworx/orderspro_sales_order_grid_renderer_registry',                
                        'type'  => 'options',
                        'options' => $helper->getEditedStatuses(),
                        'header' => $helper->__('Edited'),
                        'index' => 'is_edited',
                        'align' => 'center'
                        ));
                break;
            
                case 'order_comment':
                    $this->addColumn('order_comment', array(                            
                        'renderer'  => 'mageworx/orderspro_sales_order_grid_renderer_comments',
                        'header' => $helper->__('Order Comment(s)'),
                        'index' => 'order_comment',
						'filter_index' => 'ordercomment_table.order_comment'
                        ));
                break;
            
            }            
        }
        
        if (method_exists($this, '_prepareAmastyFlagsColumns') 
        	|| method_exists($this, '_prepareAmastyOrderattachColumns') 
        	|| method_exists($this, '_prepareFraudDetectionColumns')
                || method_exists($this, '_prepareInnoextsWarehouseColumns')
        	|| method_exists($this, '_prepareSalesRepColumns')
        	|| method_exists($this, '_prepareAWOrdertagsColumns')
        	|| method_exists($this, '_prepareAmastyOrderattrColumns')
                || method_exists($this, '_prepareDeliveryColumn')
        	) {
            $actionsColumn = null;
            if (isset($this->_columns['action'])) {
                $actionsColumn = $this->_columns['action'];
                unset($this->_columns['action']);
            }        
            if (method_exists($this, '_prepareAmastyFlagsColumns')) $this->_prepareAmastyFlagsColumns();
            if (method_exists($this, '_prepareAmastyOrderattachColumns')) $this->_prepareAmastyOrderattachColumns();
            if (method_exists($this, '_prepareFraudDetectionColumns')) $this->_prepareFraudDetectionColumns();
            if (method_exists($this, '_prepareInnoextsWarehouseColumns')) $this->_prepareInnoextsWarehouseColumns();
            if (method_exists($this, '_prepareSalesRepColumns')) $this->_prepareSalesRepColumns();
            if (method_exists($this,'_prepareAWOrdertagsColumns')) $this->_prepareAWOrdertagsColumns();
            if (method_exists($this,'_prepareAmastyOrderattrColumns')) $this->_prepareAmastyOrderattrColumns();
            if (method_exists($this,'_prepareDeliveryColumn')) $this->_prepareDeliveryColumn();
            
            if ($actionsColumn) $this->_columns['action'] = $actionsColumn;
        }
        
        $this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));
        
        $this->sortColumnsByOrder();
                
        return $this;
    }
    
    public function getCountryNames() {
        if (Mage::registry('country_names')) return Mage::registry('country_names');
        $countryNames = array();
        $collection = Mage::getResourceModel('directory/country_collection')->load();
        foreach ($collection as $item) {
            if ($item->getCountryId()) $countryNames[$item->getCountryId()] = $item->getName();
        }
        asort($countryNames);
        Mage::register('country_names', $countryNames);
        return $countryNames;
    }
    
    protected function _prepareMassaction() {
        parent::_prepareMassaction();
        $block = $this->getMassactionBlock();
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('cancel_order', array(
                 'label'=> Mage::helper('sales')->__('Cancel'),
                 'url'  => $this->getUrl('*/sales_order/massCancel'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/hold')) {
            $this->getMassactionBlock()->addItem('hold_order', array(
                 'label'=> Mage::helper('sales')->__('Hold'),
                 'url'  => $this->getUrl('*/sales_order/massHold'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/unhold')) {
            $this->getMassactionBlock()->addItem('unhold_order', array(
                 'label'=> Mage::helper('sales')->__('Unhold'),
                 'url'  => $this->getUrl('*/sales_order/massUnhold'),
            ));
        }

        $this->getMassactionBlock()->addItem('pdforders_order', array(
             'label'=> Mage::helper('sales')->__('Print Orders'),
             'url'  => $this->getUrl('*/sales_order/pdforders'),
        ));

        $this->getMassactionBlock()->addItem('pdfinvoices_order', array(
             'label'=> Mage::helper('sales')->__('Print Invoices'),
             'url'  => $this->getUrl('*/sales_order/pdfinvoices'),
        ));

        $this->getMassactionBlock()->addItem('pdfshipments_order', array(
             'label'=> Mage::helper('sales')->__('Print Packingslips'),
             'url'  => $this->getUrl('*/sales_order/pdfshipments'),
        ));

        $this->getMassactionBlock()->addItem('pdfcreditmemos_order', array(
             'label'=> Mage::helper('sales')->__('Print Credit Memos'),
             'url'  => $this->getUrl('*/sales_order/pdfcreditmemos'),
        ));

        $this->getMassactionBlock()->addItem('pdfdocs_order', array(
             'label'=> Mage::helper('sales')->__('Print All'),
             'url'  => $this->getUrl('*/sales_order/pdfdocs'),
        ));

        $this->getMassactionBlock()->addItem('print_shipping_label', array(
             'label'=> Mage::helper('sales')->__('Print Shipping Labels'),
             'url'  => $this->getUrl('*/sales_order_shipment/massPrintShippingLabel'),
        ));

       if (Mage::helper('orderspro')->isEnabled()) {                
            
            if (Mage::helper('orderspro')->isEnableInvoiceOrders() && Mage::getSingleton('admin/session')->isAllowed('sales/orderspro/actions/invoice')) {            
                $block->addItem('invoice_order', array(
                     'label'=> Mage::helper('orderspro')->__('Invoice'),
                     'url'  => $this->getUrl('*/sales_order/massInvoice'),
                ));
            }
            
            if (Mage::helper('orderspro')->isEnableShipOrders() && Mage::getSingleton('admin/session')->isAllowed('sales/orderspro/actions/ship')) {            
                $block->addItem('ship_order', array(
                     'label'=> Mage::helper('orderspro')->__('Ship'),
                     'url'  => $this->getUrl('*/sales_order/massShip'),
                ));
            }
            
            if (Mage::helper('orderspro')->isEnableInvoiceOrders() && Mage::helper('orderspro')->isEnableShipOrders() && Mage::getSingleton('admin/session')->isAllowed('sales/orderspro/actions/invoice_and_ship')) {            
                $block->addItem('invoice_and_ship_order', array(
                     'label'=> Mage::helper('orderspro')->__('Invoice+Ship'),
                     'url'  => $this->getUrl('*/sales_order/massInvoiceAndShip'),
                ));
            }
            
            if (Mage::helper('orderspro')->isEnableArchiveOrders() && Mage::getSingleton('admin/session')->isAllowed('sales/orderspro/actions/archive')) {            
                $this->getMassactionBlock()->addItem('archive_order', array(
                     'label'=> Mage::helper('orderspro')->__('Archive'),
                     'url'  => $this->getUrl('*/sales_order/massArchive'),
                ));
            }
        

            if (Mage::helper('orderspro')->isEnableDeleteOrders() && Mage::getSingleton('admin/session')->isAllowed('sales/orderspro/actions/delete')) {
                $block->addItem('delete_order', array(
                     'label'=> Mage::helper('orderspro')->__('Delete'),
                     'url'  => $this->getUrl('*/sales_order/massDelete'),
                ));
            }
            
            if (Mage::helper('orderspro')->isEnableDeleteOrdersCompletely() && Mage::getSingleton('admin/session')->isAllowed('sales/orderspro/actions/delete_completely')) {
                $block->addItem('delete_order_completely', array(
                     'label'=> Mage::helper('orderspro')->__('Delete Completely'),
                     'url'  => $this->getUrl('*/sales_order/massDeleteCompletely'),
                ));
            }
            
            
            if ((Mage::helper('orderspro')->isEnableArchiveOrders() || Mage::helper('orderspro')->isEnableDeleteOrders()) && (Mage::getSingleton('admin/session')->isAllowed('sales/orderspro/actions/archive') || Mage::getSingleton('admin/session')->isAllowed('sales/orderspro/actions/delete'))) {
                $block->addItem('restore_order', array(
                     'label'=> Mage::helper('orderspro')->__('Restore'),
                     'url'  => $this->getUrl('*/sales_order/massRestore'),
                ));
            }
            
        }
        
        // for old version amasty flags
//        if (method_exists($this, '_prepareAmastyFlagsColumns')) {
//            $block->addItem('amflags_separator_pre' . $i, array(
//                'label'=> '---------------------',
//                'url'  => '' 
//            )); 
//
//            $collection = Mage::getModel('amflags/flag')->getCollection();
//            foreach ($collection as $flag) {
//                $block->addItem('amflags_apply_' . $flag->getEntityId(), array(
//                    'label'      => 'Apply "' . $flag->getAlias() . '" Flag',
//                    'url'        => Mage::helper('adminhtml')->getUrl('amflags/adminhtml_flag/massApply', array('flag' => $flag->getEntityId())), 
//                ));
//            }
//
//            $block->addItem('amflags_remove', array(
//                'label'      => 'Remove Flag',
//                'url'        => Mage::helper('adminhtml')->getUrl('amflags/adminhtml_flag/massApply', array('flag' => 0)), 
//            ));
//
//            $block->addItem('amflags_separator_post' . $i, array(
//                'label'=> '---------------------',
//                'url'  => '' 
//            )); 
//        }
        
        return $this;
    }
}