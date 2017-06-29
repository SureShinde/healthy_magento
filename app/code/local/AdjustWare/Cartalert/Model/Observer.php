<?php
/**
 * Abandoned Carts Alerts Pro
 *
 * @category:    AdjustWare
 * @package:     AdjustWare_Cartalert
 * @version      3.2.0
 * @license:     G6aOfhcOapJAn8TGHRhFIbUCRgt8VW93TqhNqwDF4t
 * @copyright:   Copyright (c) 2014 AITOC, Inc. (http://www.aitoc.com)
 */
/**
 * Cartalert module observer
 *
 * @author Adjustware
 */
class Adjustware_Cartalert_Model_Observer
{
    public function createCartalerts()
    {
        $cartalert = Mage::getModel('adjcartalert/cartalert');
        $cartalert->generate(date('Y-m-d H:i:s'));
        
        //added in 1.2.1
        $this->sendCartalerts();
        
        return $this;
    }

    public function runStat()
    {
        Mage::getModel('adjcartalert/cronstat')->cron();
        return $this;
    }    
    
    public function sendCartalerts()
    {
        if (!Mage::getStoreConfig('catalog/adjcartalert/sending_enabled'))
            return $this;
        $collection = Mage::getModel('adjcartalert/cartalert')->getCollection()
            ->addReadyForSendingFilter() 
            ->setPageSize(50)
            ->setCurPage(1)
            ->load();
        foreach ($collection as $cartalert){
            if ($cartalert->send()){
                $cartalert->delete(); 
            } 
        }  
        return $this;
    }
    
    public function processOrderCreated($observer){
        $order = $observer->getEvent()->getOrder(); 
        
        if (Mage::getStoreConfig('catalog/adjcartalert/stop_after_order')){
            $cartalert = Mage::getResourceModel('adjcartalert/cartalert')
                ->cancelAlertsFor($order->getCustomerEmail());
        }
        return $this;

    } 
    
    public function updateAlertsStatus($observer)
    {
    	if (!Mage::registry('alerts_status_updated'))
    	{
    		Mage::register('alerts_status_updated', true);
    		
			$quote = Mage::getSingleton('checkout/session')->getQuote();
			
			if ($quote)
			{
				$quote->setAllowAlerts(1);
				
				if (Mage::getStoreConfig('catalog/adjcartalert/stop_after_order')){
		            $cartalert = Mage::getResourceModel('adjcartalert/cartalert')
		                ->cancelAlertsFor($quote->getCustomerEmail());
		        }
			}
    	}
		
        return $this;
    }
}