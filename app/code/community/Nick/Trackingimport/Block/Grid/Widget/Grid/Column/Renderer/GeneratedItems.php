<?php

class Nick_Trackingimport_Block_Grid_Widget_Grid_Column_Renderer_GeneratedItems extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
	/**
	 * Render column
	 *
	 * @param Varien_Object $row
	 * @return unknown
	 */
    public function render(Varien_Object $row)
    {
    	$return = '<div class="nowrap">';
    	
    	$invoices = $this->getInvoice($row);
    	
    	//display invoice info
    	if (!$invoices)
    		$return .= '<font color="red">'.$this->__('No invoices').'</font>';
    	else 
    	{
    		foreach ($invoices as $invoice){
				$return .= '<a href="'.$this->getUrl('adminhtml/sales_order_invoice/print', array('invoice_id' => $invoice->getId())).'">'.$this->__('Invoice %s', $invoice->getincrement_id()).'</a><br />';
			}
		}
    	
    	$return .= '</div>';
  
    	
        return $return;
    }
    
    /**
     * Return first invoice
     *
     * @param unknown_type $order
     * @return unknown
     */
	
	protected function getInvoice($order)
    {
    	$invoices = NULL;
		$invoices = Mage::getResourceModel('sales/order_invoice_collection')
                    ->addAttributeToSelect('*')
                    ->setOrderFilter($order->getId());
        
		if(!$this->getTrackingGridConfig('invoices'))$shipments->setOrder('created_at', 'DESC')->setPageSize(1); 
					
		$invoices->load();
		
		return $invoices;
    }
    
	
	public function getTrackingGridConfig($option){
		return Mage::getStoreConfig('trackingimport/trackinggrid/'.$option);
	}
    
}