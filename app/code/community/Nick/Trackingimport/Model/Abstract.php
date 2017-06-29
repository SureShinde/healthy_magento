<?php

class Nick_Trackingimport_Model_Abstract
{
  
  public function Ftplogin()
  {
		$ftp_server = Mage::getStoreConfig('trackingimport/ftp/ftp_host');
		$ftp_user_name = Mage::getStoreConfig('trackingimport/ftp/ftp_username');
		$ftp_user_pass = Mage::getStoreConfig('trackingimport/ftp/ftp_password');
		$port = Mage::getStoreConfig('trackingimport/ftp/ftp_port');
		
		$file = NULL;
		
		// set up basic connection
		
		$conn_id = ftp_connect($ftp_server, $port);  
		
		if (!$conn_id)	return; 
				
		// login with username and password
		$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 
		
		// check connection, if fails return
		if (!$login_result)	return; 
		
		//check if passive is enabled
		if (Mage::getStoreConfig('trackingimport/ftp/enable_pass') == 1) ftp_pasv( $conn_id, true );
				
		$dir = Mage::getStoreConfig('trackingimport/ftp/ftp_remotedir');
    	// get contents of the current directory
		$file_list = ftp_nlist($conn_id, "$dir/*.csv");
		
		sort($file_list);
    	
    	foreach ($file_list as $file)
		{
  			//if directory empty, return
			if (!$file) return;
			
			$localFilename = date("dmy-hmi").".csv"; 	

			$local_dir  = Mage::getBaseDir().'/var/import/'.Mage::getStoreConfig('trackingimport/cron_settings/localdir').'/';
			
			if (!file_exists($local_dir . $file)) {
            	
				$destination_file = $local_dir . $file;
				
				$is_copied = ftp_get($conn_id, $destination_file, $file, FTP_BINARY);
        		
				chmod($destination_file,0777);
    		
		    	$delete = ftp_delete($conn_id, $file);
			
			}
			
		}

		// close the FTP stream 
		ftp_close($conn_id); 
		
		return;
  }
}