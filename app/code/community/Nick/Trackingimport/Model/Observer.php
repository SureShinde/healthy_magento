<?php

class Nick_Trackingimport_Model_Observer extends Nick_Trackingimport_Model_Abstract
{
  
  public function Fileimport()
  {
  
  	if(Mage::getStoreConfig('trackingimport/cron_settings/active') == 1){
  	
		if(Mage::getStoreConfig('trackingimport/ftp/enable_ftp')) $this->Ftplogin();
		
		$files = $this->Getfiles();
		if ($files) {
		
		$localdir = Mage::getStoreConfig('trackingimport/cron_settings/localdir');
		
		foreach ($files as $fileName){
			
			$target_path = Mage::getBaseDir().'/var/import/'.$localdir.'/'.$fileName[0];
	
			$csvObject  = new Varien_File_Csv();
			$csvObject->setDelimiter(Mage::getStoreConfig('trackingimport/general/delimiter'));
			$csvObject->setEnclosure(Mage::getStoreConfig('trackingimport/general/enclosure'));
			$csvData = $csvObject->getData($target_path);
      		
			$csvFields  = array(
				0   => Mage::getStoreConfig('trackingimport/csvheaders/orderid'),
				1   => Mage::getStoreConfig('trackingimport/csvheaders/shipmentid'),
				2   => Mage::getStoreConfig('trackingimport/csvheaders/carrierid'),
			);
        
	   			
			foreach ($csvData as $k => $v) {

                $orderId = $v[0];
                    $trackingNum = $v[1];
                    $carrierTitle = $v[2];

                    if (Mage::getStoreConfig('trackingimport/general/skip') == 1){
                                if ($k == 0) {
                                    continue;
                                }
                    }

                    try {

                        Mage::getModel('Trackingimport/import')->BeginImport($orderId, $trackingNum, $carrierTitle);

                    } catch (Mage_Core_Exception $e) {
                        Mage::log("$e->getMessage()");
                       return;
                    }

			} 
  		
			 if (Mage::getStoreConfig('trackingimport/cron_settings/cron_archive') == 1) {
				$new_path = Mage::getBaseDir().'/var/import/'.$localdir.'/imported/archived'.date("d-m-y-h-i").".csv";
				copy($target_path, $new_path);
			 }
			
			 unlink($target_path);
		}
		}
  		return;
  	}
  }
  
  public function Getfiles()
  { 
   		$localdir = Mage::getStoreConfig('trackingimport/cron_settings/localdir');
   		
   		$dir= Mage::getBaseDir().'/var/import/'.$localdir; 

   		$files=Array();

		$f=opendir($dir);

   		while (($file=readdir($f))!==false) {
	   		if(is_file("$dir/$file")) $files[]=Array($file,filemtime("$dir/$file"));
		}
	   	
	   	closedir($f);
		
		return($files);
		
	}	  
}
