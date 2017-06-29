<?php
/**
 *
 * @category   Bluejalappeno
 * @package    Bluejalappeno_Orderexport
 * @copyright  Copyright (c) 2012 Wimbolt Ltd (http://www.bluejalappeno.com)
 * @license    http://www.bluejalappeno.com/license.txt - Commercial license
 */


class Bluejalappeno_Orderexport_Model_Observer
{
	public function addMassAction($observer) {
   		$block = $observer->getEvent()->getBlock();
        //if(get_class($block) =='Mage_Adminhtml_Block_Widget_Grid_Massaction'
		if($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction
            && strstr( $block->getRequest()->getControllerName(), 'sales_order') && Mage::getStoreConfig("order_export/export_orders/active")) 
        {
        	$block->addItem('orderexport', array(
                'label' => Mage::helper('sales')->__('FedEx Orders Export'),
                'url' => Mage::getModel('adminhtml/url')->getUrl('*/sales_order_export/csvexport'),
            ));

            $block->addItem('pdforders_order', array(
                'label'=> Mage::helper('sales')->__('Print Orders'),
                'url'  => Mage::getModel('adminhtml/url')->getUrl('*/sales_order/pdforders'),
            ));
        }
       
   	
   }

}