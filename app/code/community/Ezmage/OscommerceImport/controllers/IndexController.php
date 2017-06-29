<?php
class Ezmage_OscommerceImport_IndexController extends Mage_Core_Controller_Front_Action
{
		public $global_shopping_cart;
		
    public function indexAction()
    {						
		
		Mage::register('image_status1','check-no.jpg');
		Mage::register('image_status2','check-no.jpg');
		Mage::register('image_status3','check-no.jpg');
		Mage::register('image_status4','check-no.jpg');
		Mage::register('image_status5','check-no.jpg');
		Mage::register('image_status6','check-no.jpg');
		Mage::register('image_status7','check-no.jpg');
		Mage::register('image_status8','check-no.jpg');														
		
		//check if configuration has values
		$conf_hostname 			= Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_hostname',Mage::app()->getStore());
		$conf_port 				= Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_port',Mage::app()->getStore());
		$conf_db 				= Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_db',Mage::app()->getStore());
		$conf_db_username 		= Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_username',Mage::app()->getStore());
		$conf_db_password 		= Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_password',Mage::app()->getStore());
		//$conf_table_prefix 		= Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_prefix',Mage::app()->getStore());
		$conf_website 			= Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_website',Mage::app()->getStore());
		$conf_category 			= Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_category',Mage::app()->getStore());
		$conf_attribute 		= Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_attribute',Mage::app()->getStore());
		

			
		if ( ($conf_hostname != "") and ($conf_db != "") and ($conf_db_username != "") and ($conf_db_password != "")){		
			Mage::register('conf_hostname',$conf_hostname);
			Mage::register('conf_port',$conf_port);
			Mage::register('conf_db',$conf_db);
			Mage::register('conf_db_username',$conf_db);
			Mage::register('conf_db_password',$conf_db_password);
			//Mage::register('conf_table_prefix',$conf_table_prefix);
			Mage::register('conf_website',$conf_website);	
			Mage::register('conf_category',$conf_category);	
			Mage::register('conf_attribute',$conf_attribute);
			
			Mage::unregister('image_status1');
			Mage::register('image_status1','check-ok.jpg');												
		} 
		
		// step 2
		$step2Status=Mage::getSingleton('core/session')->getstep2Status();
		if ($step2Status == 'ok'){
			Mage::unregister('image_status2');
			Mage::register('image_status2','check-ok.jpg');
		}
		
		//step3
		$importCategoryTotal = Mage::getSingleton('core/session')->getimportCategoryTotal();
		$importProductsTotal = Mage::getSingleton('core/session')->getimportProductsTotal();
		if (($importCategoryTotal > 0) or ($importProductsTotal > 0)){
			Mage::unregister('image_status3');
			Mage::register('image_status3','check-ok.jpg');
		}
		
		// step4
		$importCategoryTotal = Mage::getSingleton('core/session')->getimportCategoryTotal();
		$importedCategoryTotal = Mage::getSingleton('core/session')->getimportedCategoryTotal();
		if ( ($importCategoryTotal == $importedCategoryTotal) and ($importCategoryTotal > 0))  {
			Mage::unregister('image_status4');
			Mage::register('image_status4','check-ok.jpg');
		}
			
		// step5
		$importProductsTotal = Mage::getSingleton('core/session')->getimportProductsTotal();
		$importedProductsTotal = Mage::getSingleton('core/session')->getimportedProductsTotal();
		if (($importProductsTotal == $importedProductsTotal) and ($importProductsTotal > 0)) {
			Mage::unregister('image_status5');
			Mage::register('image_status5','check-ok.jpg');
		}

		// step6
		$importCustomersTotal = Mage::getSingleton('core/session')->getimportCustomersTotal();
		$importedCustomersTotal = Mage::getSingleton('core/session')->getimportedCustomersTotal();
		if (($importCustomersTotal == $importedCustomersTotal) and ($importCustomersTotal > 0)) {
			Mage::unregister('image_status6');
			Mage::register('image_status6','check-ok.jpg');
		}

		// step7
		$importOrdersTotal = Mage::getSingleton('core/session')->getimportOrdersTotal();
		$importedOrdersTotal = Mage::getSingleton('core/session')->getimportedOrdersTotal();
		if (($importOrdersTotal == $importedOrdersTotal) and ($importOrdersTotal > 0)) {
			Mage::unregister('image_status7');
			Mage::register('image_status7','check-ok.jpg');
		}																				
		
								
		// Layout Out Put			
		$this->loadLayout();
		
    	$block = $this->getLayout()->createBlock(
		    'Mage_Core_Block_Template',
		    'block_ezmage_oscommerce_import_step1',
			array('template' => 'ezmage/oscommerceimport/step0.phtml')
		);
    	
    $this->getLayout()->getBlock('content')->append($block);
		$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');			
			
		$this->renderLayout();		
    }
	
	/*******************************************************************************/
	/* Step 1			 														   																	*/
	/*******************************************************************************/
    public function step1Action()
    {
			
		Mage::getSingleton('core/session')->setErrorMSG('');
			
		$this->loadLayout();
		
    	$block = $this->getLayout()->createBlock(
		    'Mage_Core_Block_Template',
		    'block_ezmage_oscommerce_import_step1',
			array('template' => 'ezmage/oscommerceimport/step1.phtml')
		);
    	
    $this->getLayout()->getBlock('content')->append($block);			
		$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
					
		$this->renderLayout();
    }
	
	/*******************************************************************************/
	/* Step 2																				 														   */
	/*******************************************************************************/
    public function step2Action()
    {
		
		Mage::getSingleton('core/session')->setErrorMSG('');
			
		// check if tmp are created
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
	 	$writeConnection = $resource->getConnection('core_write');
	 
		$query = 'show tables like "ezmage_categories"';
		$list = $readConnection->fetchAll($query);
		$ezmage_categories = sizeof($list);
		if ($ezmage_categories == 0){
			$query = "CREATE TABLE IF NOT EXISTS `ezmage_categories` (`osc_cat_id` int(11) NOT NULL,`osc_cat_title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,`osc_cat_parent` int(11) NOT NULL,`mage_cat_id` int(11) NOT NULL,`mage_cat_name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,`mage_cat_parent` int(11) NOT NULL,`cat_imported` varchar(1) COLLATE utf8_unicode_ci NOT NULL,`osc_cat_image` varchar(200) COLLATE utf8_unicode_ci NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	    	$writeConnection->query($query);
		}
		
		$query = 'show tables like "ezmage_products"';
		$list = $readConnection->fetchAll($query);
		$ezmage_products = sizeof($list);
		if ($ezmage_products == 0){
			$query = "CREATE TABLE IF NOT EXISTS `ezmage_products` (`osc_product_id` int(11) NOT NULL,`osc_product_name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,`mage_product_id` int(11) NOT NULL,`product_imported` varchar(1) COLLATE utf8_unicode_ci NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	    $writeConnection->query($query);
		}
		
		$query = 'show tables like "ezmage_customers"';
		$list = $readConnection->fetchAll($query);
		$ezmage_products = sizeof($list);
		if ($ezmage_products == 0){
			$query = "CREATE TABLE IF NOT EXISTS `ezmage_customers` (`osc_customers_id` int(11) NOT NULL,`mage_customers_id` int(11) NOT NULL,`customers_imported` varchar(1) COLLATE utf8_unicode_ci NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	    $writeConnection->query($query);
		}		

		$query = 'show tables like "ezmage_orders"';
		$list = $readConnection->fetchAll($query);
		$ezmage_products = sizeof($list);
		if ($ezmage_products == 0){
			$query = "CREATE TABLE IF NOT EXISTS `ezmage_orders` (`osc_order_id` int(11) NOT NULL,`mage_order_id` int(11) NOT NULL,`order_imported` varchar(1) COLLATE utf8_unicode_ci NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	    $writeConnection->query($query);
		}		
		
		$query = 'show tables like "ezmage_log"';
		$list = $readConnection->fetchAll($query);
		$ezmage_log = sizeof($list);
		if ($ezmage_log == 0){
			$query = "CREATE TABLE IF NOT EXISTS `ezmage_log` (`importtime` datetime NOT NULL,`log_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,`log_desc` varchar(200) COLLATE utf8_unicode_ci NOT NULL,`mage_log` varchar(255) COLLATE utf8_unicode_ci NOT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	    	$writeConnection->query($query);
		}
		
				
		if ( ($ezmage_categories == 1) and ($ezmage_products == 1) and ($ezmage_log == 1) ){
			$step2Status = 'ok';
			Mage::getSingleton('core/session')->setstep2Status($step2Status);
											
		}
		
		$this->loadLayout();
		
    	$block = $this->getLayout()->createBlock(
		    'Mage_Core_Block_Template',
		    'block_ezmage_oscommerce_import_step2',
			array('template' => 'ezmage/oscommerceimport/step2.phtml')
		);
    	
    $this->getLayout()->getBlock('content')->append($block);			
		$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
					
		$this->renderLayout();
    }
	
	
	/*******************************************************************************/
	/* Step 2.1																			 														   */
	/*******************************************************************************/
    public function step21Action()
    {										
			
			Mage::getSingleton('core/session')->setErrorMSG('');
			
			try{
				// check if tmp are created
				$resource = Mage::getSingleton('core/resource');
				$writeConnection = $resource->getConnection('core_write');
				// Remove tmp tables
				$query = 'TRUNCATE TABLE ezmage_categories';
				$writeConnection->query($query);
				$query = 'TRUNCATE TABLE ezmage_log';
				$writeConnection->query($query);
				$query = 'TRUNCATE TABLE ezmage_products';
				$writeConnection->query($query);
				$query = 'TRUNCATE TABLE ezmage_customers';
				$writeConnection->query($query);	
				$query = 'TRUNCATE TABLE ezmage_orders';
				$writeConnection->query($query);														
			}		
			catch (Exception $ex) {			
				echo $ex->getMessage();
			}										
			
			Mage::register('truncate_tables','yes');
			Mage::getSingleton('core/session')->unsimportCategoryTotal();
			Mage::getSingleton('core/session')->unsimportProductsTotal();
			Mage::getSingleton('core/session')->unsimportCategoryTotal();
			Mage::getSingleton('core/session')->unsimportedCategoryTotal();
			Mage::getSingleton('core/session')->unsimportProductsTotal();
			Mage::getSingleton('core/session')->unsimportedProductsTotal();
			Mage::getSingleton('core/session')->unsimportedOrdersTotal();
														
			$this->loadLayout();
			
				$block = $this->getLayout()->createBlock(
					'Mage_Core_Block_Template',
					'block_ezmage_oscommerce_import_step4',
				array('template' => 'ezmage/oscommerceimport/step2.phtml')
			);
				
			$this->getLayout()->getBlock('content')->append($block);			
			$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
						
			$this->renderLayout();
    }	
		
			
	/*******************************************************************************/
	/* Step 3			 														   																	*/
	/*******************************************************************************/
    public function step3Action()
    {
		
		Mage::getSingleton('core/session')->setErrorMSG('');
		
		$_config = $this->setRemoteConectionConfig();
		try {
			$_connection = Mage::getSingleton('core/resource')->createConnection('oscommerce_conection', 'pdo_mysql', $_config);
			$query = 'show tables like "ezmage_log"';
			$list = $_connection->fetchAll($query);
			Mage::register('conection_status',"ok");
		}
		catch (Exception $ex) {
			 Mage::register('conection_status',$ex->getMessage());
		}
		
		$this->loadLayout();
		
    	$block = $this->getLayout()->createBlock(
		    'Mage_Core_Block_Template',
		    'block_ezmage_oscommerce_import_step3',
			array('template' => 'ezmage/oscommerceimport/step3.phtml')
		);
    	
    	$this->getLayout()->getBlock('content')->append($block);			
		$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
					
		$this->renderLayout();
    }
	
	/*******************************************************************************/
	/* Step 3.1			 														   																*/
	/*******************************************************************************/
    public function step31Action()
    {
		
		Mage::getSingleton('core/session')->setErrorMSG('');
			
		$_config = $this->setRemoteConectionConfig();
		try {
				
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
			$writeConnection = $resource->getConnection('core_write');							
			$_connection_remote = Mage::getSingleton('core/resource')->createConnection('oscommerce_conection', 'pdo_mysql', $_config);			
			$conf_language_id	= Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_language_id',Mage::app()->getStore());
			
			////////////////
			// categories //
			////////////////			
			$query = 'select categories.categories_id ,categories.categories_image, categories.parent_id, categories_description.categories_name from categories LEFT JOIN categories_description on categories_description.categories_id = categories.categories_id and categories_description.language_id = '.$conf_language_id;
			$results  = $_connection_remote->fetchAll($query);
			foreach($results as $row) {
				// check if category exist
				$sql = "select osc_cat_id from ezmage_categories where osc_cat_id=".$row['categories_id'];
				$list = $readConnection->fetchAll($sql);
				if (sizeof($list) == 0){
					$row['categories_name'] = str_replace('"','',$row['categories_name']);
					$row['categories_name'] = str_replace('\"','',$row['categories_name']);
					$sql = 'insert into ezmage_categories (osc_cat_id,osc_cat_title,osc_cat_parent,osc_cat_image) values('.$row['categories_id'].',"'.$row['categories_name'].'","'.$row['parent_id'].'","'.$row['categories_image'].'")';
					$writeConnection->query($sql);				
				}
			}						
			$sql = "select osc_cat_id from ezmage_categories";
			$list = $readConnection->fetchAll($sql);
			$importCategoryTotal = sizeof($list);			
			Mage::getSingleton('core/session')->setimportCategoryTotal($importCategoryTotal);
			
			//////////////
			// products //
			//////////////
			$query = 'select products_id,products_name from products_description where language_id = '.$conf_language_id;
			$results  = $_connection_remote->fetchAll($query);
			foreach($results as $row) {
				// check if category exist
				$sql = "select osc_product_id from ezmage_products where osc_product_id=".$row['products_id'];
				$list = $readConnection->fetchAll($sql);
				if (sizeof($list) == 0){
					//$row['products_name'] = str_replace('\"','',$row['products_name']);
					//$row['products_name'] = str_replace('"','',$row['products_name']);
					//$row['products_name'] = str_replace('"','',$row['products_name']);
					$products_name = mysql_escape_string($row['products_name']);
					//print $row['products_name'].' - '.mysql_escape_string($row['products_name']);exit;
					$sql = 'insert into ezmage_products values('.$row['products_id'].',"'.$products_name.'","","")';
					$writeConnection->query($sql);				
				}
			}	
			
			$sql = "select osc_product_id from ezmage_products";
			$list = $readConnection->fetchAll($sql);
			$importProductsTotal = sizeof($list);			
			Mage::getSingleton('core/session')->setimportProductsTotal($importProductsTotal);
			
			///////////////
			// Customers //
			///////////////
			$query = 'select customers_id from customers';
			$results  = $_connection_remote->fetchAll($query);
			foreach($results as $row) {
				// check if customers exist
				$sql = "select osc_customers_id from ezmage_customers where osc_customers_id=".$row['customers_id'];
				$list = $readConnection->fetchAll($sql);
				if (sizeof($list) == 0){
					$sql = 'insert into ezmage_customers values('.$row['customers_id'].',"","")';
					$writeConnection->query($sql);				
				}
			}			
			
			$sql = "select osc_customers_id from ezmage_customers";
			$list = $readConnection->fetchAll($sql);
			$importCustomersTotal = sizeof($list);		
			Mage::getSingleton('core/session')->setimportCustomersTotal($importCustomersTotal);	
			
			///////////////
			// Customers //
			///////////////
			$query = 'select orders_id from orders';
			$results  = $_connection_remote->fetchAll($query);
			foreach($results as $row) {
				// check if customers exist
				$sql = "select 	osc_order_id from ezmage_orders where 	osc_order_id=".$row['orders_id'];
				$list = $readConnection->fetchAll($sql);
				if (sizeof($list) == 0){
					$sql = 'insert into ezmage_orders values('.$row['orders_id'].',"","")';
					$writeConnection->query($sql);				
				}
			}	
						
			$sql = "select osc_order_id from ezmage_orders";
			$list = $readConnection->fetchAll($sql);
			$importOrdersTotal = sizeof($list);			
			Mage::getSingleton('core/session')->setimportOrdersTotal($importOrdersTotal);						
								
			
		}
		catch (Exception $ex) {
			Mage::getSingleton('core/session')->setErrorMSG($ex->getMessage());										
		}
		
		$this->loadLayout();
		
    	$block = $this->getLayout()->createBlock(
		    'Mage_Core_Block_Template',
		    'block_ezmage_oscommerce_import_step31',
			array('template' => 'ezmage/oscommerceimport/step31.phtml')
		);
    	
    	$this->getLayout()->getBlock('content')->append($block);			
		$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
					
		$this->renderLayout();
    }
	
	
	/*******************************************************************************/
	/* Step 4			 														   																	*/
	/*******************************************************************************/
    public function step4Action()
    {
		
		Mage::getSingleton('core/session')->setErrorMSG('');
							
		try {			
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
										
			$sql = "select osc_cat_id from ezmage_categories where cat_imported='y'";
			$list = $readConnection->fetchAll($sql);
			$importedCategoryTotal = sizeof($list);		
			Mage::getSingleton('core/session')->setimportedCategoryTotal($importedCategoryTotal);											
		}
		catch (Exception $ex) {
			//Mage::register('conection_status',$ex->getMessage());
			echo $ex->getMessage();
		}										
				
		$this->loadLayout();
		
    	$block = $this->getLayout()->createBlock(
		    'Mage_Core_Block_Template',
		    'block_ezmage_oscommerce_import_step4',
			array('template' => 'ezmage/oscommerceimport/step4.phtml')
		);
    	
    	$this->getLayout()->getBlock('content')->append($block);			
		$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
					
		$this->renderLayout();
    }	
	
	
	/*******************************************************************************/
	/* Step 4.1			 														   																	*/
	/*******************************************************************************/
    public function step41Action()
    {			
		
		Mage::getSingleton('core/session')->setErrorMSG('');	
						
		// Stop Indexes     
		// http://www.clounce.com/magento/magento-reindex-programmatically
		// http://stackoverflow.com/questions/5420552/magento-programmatically-disable-automatic-indexing
		$pCollection = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
		foreach ($pCollection as $process) {
			$process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
		}
		
		$totalimport = 0;
		
		try {			
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
			$writeConnection = $resource->getConnection('core_write');
			
			$storeId = Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_website',Mage::app()->getStore());

			$categories_array = array();
			$this->tep_get_subcategories(&$categories_array, 0, $readConnection);
			foreach($categories_array as $category_row){
				if ($category_row['cat_imported'] != 'y'){
					//print $category_row['osc_cat_id'].' => '.$category_row['osc_cat_title'].' => '.$category_row['osc_cat_parent'].'<br>';				
					$category = Mage::getModel( 'catalog/category' );
					$category->setStoreId( $storeId );
					$category->setName($category_row['osc_cat_title']);
					$category->setIsActive(1);
					$category->setIsAnchor(0);
					$category->setIncludeInMenu(0);
					$category->setDisplayMode('PRODUCTS');
					// getIncludeInMenu
					
					if ($category_row['osc_cat_parent'] != 0){
						$parent_id = $this->getMageParentID($category_row['osc_cat_parent'],$readConnection);		
						$parent = Mage::getModel('catalog/category')->load($parent_id);	
					}else{
						$parent = Mage::getModel('catalog/category')->load(Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_category',Mage::app()->getStore()));	
					} 
					$category->setPath( $parent->getPath() ); 
					
					$path = $this->getDownloadImage("category",$category_row['osc_cat_image']);
					if ($category_row['osc_cat_image'] != ""){
						$data['thumbnail'] = $path;
						$category->addData($data);  
					}
									
					$category->save();
									
					// update tmp table
					$sql = "update ezmage_categories set cat_imported='y',mage_cat_id=".$category->getId().", mage_cat_parent=".$parent->getId()." where osc_cat_id=".$category_row['osc_cat_id'];				
					$writeConnection->query($sql);	
					
					$totalimport++;
					if ($totalimport == Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_totalperimportcat',Mage::app()->getStore())){
						break;
					}	
				}
			}  
									
						
			// get import status							
			$sql = "select osc_cat_id from ezmage_categories where cat_imported='y'";
			$list = $readConnection->fetchAll($sql);
			$importedCategoryTotal = sizeof($list);		
			Mage::getSingleton('core/session')->setimportedCategoryTotal($importedCategoryTotal);	
												
		}
		catch (Exception $ex) {
			Mage::getSingleton('core/session')->setErrorMSG('osCommerce Category ID: '.$category_row['osc_cat_id'].'<br>Name: '.$category_row['osc_cat_title'].'<br>Exception: '.$ex->getMessage());										
		}										
				
		// Start Indexes		
		foreach ($pCollection as $process) {
		  $process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
		}
													
		$this->loadLayout();
		
    	$block = $this->getLayout()->createBlock(
		    'Mage_Core_Block_Template',
		    'block_ezmage_oscommerce_import_step4',
			array('template' => 'ezmage/oscommerceimport/step4.phtml')
		);
    	
    $this->getLayout()->getBlock('content')->append($block);			
		$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
					
		$this->renderLayout();		
    }	
	

	/*******************************************************************************/
	/* Step 5			 														  																	 */
	/*******************************************************************************/
    public function step5Action()
    {
		
		Mage::getSingleton('core/session')->setErrorMSG('');
							
		try {			
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
										
			$sql = "select osc_product_id from ezmage_products where product_imported='y'";
			$list = $readConnection->fetchAll($sql);
			$importedProductsTotal = sizeof($list);		
			Mage::getSingleton('core/session')->setimportedProductsTotal($importedProductsTotal);											
		}
		catch (Exception $ex) {
			//Mage::register('conection_status',$ex->getMessage());
			echo $ex->getMessage();
		}										
				
		$this->loadLayout();
		
    	$block = $this->getLayout()->createBlock(
		    'Mage_Core_Block_Template',
		    'block_ezmage_oscommerce_import_step4',
			array('template' => 'ezmage/oscommerceimport/step5.phtml')
		);
    	
    $this->getLayout()->getBlock('content')->append($block);			
		$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
					
		$this->renderLayout();
    }	
	
	/*******************************************************************************/
	/* Step 5.1			 														  																 */
	/*******************************************************************************/
    public function step51Action()
    {				
		
		Mage::getSingleton('core/session')->setErrorMSG('');	
		
		$storeId = Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_website',Mage::app()->getStore());
		$parent_cat = Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_category',Mage::app()->getStore());
		$conf_language_id	= Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_language_id',Mage::app()->getStore());
								
		$_config = $this->setRemoteConectionConfig();
		// Stop Indexes     
		// http://www.clounce.com/magento/magento-reindex-programmatically
		// http://stackoverflow.com/questions/5420552/magento-programmatically-disable-automatic-indexing
		$pCollection = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
		foreach ($pCollection as $process) {
			$process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
		}
				
		try {			
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
			$writeConnection = $resource->getConnection('core_write');
			$_connection_remote = Mage::getSingleton('core/resource')->createConnection('oscommerce_conection', 'pdo_mysql', $_config);
						
			$storeId = Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_website',Mage::app()->getStore());
			$AttributeSetId = Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_attribute',Mage::app()->getStore());
			
			$sql = "select * from ezmage_products where product_imported<>'y'";
			$results = $readConnection->fetchAll($sql);

			foreach($results as $row) {
				//print $row['osc_product_id'].' => '.$row['osc_product_name'].' => '.$row['mage_product_id'].'<br>';				
				
				$product_osc = $this->getProductFromOSC($row['osc_product_id'],$readConnection,$_connection_remote,$conf_language_id);
				$product_categories_osc = $this->getCategoriesProductFromOSC($row['osc_product_id'],$readConnection,$_connection_remote,$parent_cat);		
								
				// some validation
				if ($product_osc['products_model'] == '') {
					$product_osc['products_model'] = $row['products_id'];
				}
				
				$product_osc['products_description'] = str_replace('\"','',$product_osc['products_description']);
				
				
				//search if sku exits
				$oProduct = Mage::getModel("catalog/product")
                ->getCollection()
                ->setStoreId($storeId)
                ->addAttributeToSelect("sku")
                ->addFieldToFilter("sku", array('eq' => $product_osc['products_model']))                        
                ->getFirstItem();
				
				if ( sizeof($oProduct->getData()) > 0){
					$product_osc['products_model'] = $product_osc['products_model'].rand(1111,9999);
				}
				
				if ($product_osc['products_quantity'] < 0){
					$product_osc['products_quantity'] = 1;
				}

				//$product = Mage::getModel('catalog/product');
				$product = new Mage_Catalog_Model_Product();
				 
				// Build the product
				$product->setSku($product_osc['products_model']);
				$product->setAttributeSetId($AttributeSetId); 
				$product->setTypeId('simple');
				$product->setName($product_osc['products_name']);
				$product->setCategoryIds($product_categories_osc); 
				$product->setWebsiteIDs(array($storeId)); 
				$product->setDescription($product_osc['products_description']);
				$product->setShortDescription('-');
				$product->setPrice($product_osc['products_price']);  			 
				$product->setWeight($product_osc['products_weight']);
				 
				$product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH);
				$product->setStatus($product_osc['products_status']);
				$product->setTaxClassId(0); # My default tax class
				$product->setStockData(array(
					'is_in_stock' => 1,
					'qty' => $product_osc['products_quantity']
				));
				 
				$product->setCreatedAt(strtotime('now'));
				
				if ($product_osc['products_image'] != ''){
					$allowimages = $this->getImageAllow($product_osc['products_image']);
					if ($allowimages){ 						
						$image_location = $this->getDownloadImage("product",$product_osc['products_image']);				
						if ( file_exists($image_location) ) {
							$product->addImageToMediaGallery($image_location,array('thumbnail','small_image','image'),true,false);
						}
					}
				}
														 
				$product->save();
																								
				// update tmp table
				$sql = "update ezmage_products set product_imported='y',mage_product_id=".$product->getId()." where osc_product_id=".$row['osc_product_id'];				
				$writeConnection->query($sql);	
				
				$totalimport++;
				if ($totalimport == Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_totalperimport',Mage::app()->getStore())){
					break;
				}				
			}  									
						
			// get import status							
			$sql = "select osc_product_id from ezmage_products where product_imported='y'";
			$list = $readConnection->fetchAll($sql);
			$importedProductsTotal = sizeof($list);		
			Mage::getSingleton('core/session')->setimportedProductsTotal($importedProductsTotal);	
												
		}
		catch (Exception $ex) {
			Mage::getSingleton('core/session')->setErrorMSG('osCommerce Product ID: '.$row['osc_product_id'].'<br>Name: '.$product_osc['products_name'].'<br> Description: '.$product_osc['products_description'].'<br>Exception: '.$ex->getMessage());							
		}										
		
		
		// Start Indexes		
		foreach ($pCollection as $process) {
		  $process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
		}
								
		// Layaout		
		$this->loadLayout();
		
    	$block = $this->getLayout()->createBlock(
		    'Mage_Core_Block_Template',
		    'block_ezmage_oscommerce_import_step4',
			array('template' => 'ezmage/oscommerceimport/step5.phtml')
		);
    	
    $this->getLayout()->getBlock('content')->append($block);			
		$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
					
		$this->renderLayout();
    }
		
		

	/*******************************************************************************/
	/* Step 6																				 														   */
	/*******************************************************************************/
    public function step6Action()
    {																
			$this->loadLayout();
			Mage::getSingleton('core/session')->setErrorMSG('');	
			
		try {			
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
										
			$sql = "select * from ezmage_customers where customers_imported='y'";
			$list = $readConnection->fetchAll($sql);
			$importedCustomersTotal = sizeof($list);		
			Mage::getSingleton('core/session')->setimportedCustomersTotal($importedCustomersTotal);											
		}
		catch (Exception $ex) {
			//Mage::register('conection_status',$ex->getMessage());
			echo $ex->getMessage();
		}	
		
				$block = $this->getLayout()->createBlock(
					'Mage_Core_Block_Template',
					'block_ezmage_oscommerce_import_step4',
				array('template' => 'ezmage/oscommerceimport/step6.phtml')
			);
				
			$this->getLayout()->getBlock('content')->append($block);			
			$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
						
			$this->renderLayout();
    }	

	/*******************************************************************************/
	/* Step 6.1																			 														   */
	/*******************************************************************************/
    public function step61Action()
    {													
		
		Mage::getSingleton('core/session')->setErrorMSG('');	
						
		// Stop Indexes     
		// http://www.clounce.com/magento/magento-reindex-programmatically
		// http://stackoverflow.com/questions/5420552/magento-programmatically-disable-automatic-indexing
		$pCollection = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
		foreach ($pCollection as $process) {
			$process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
		}
		
		$_config = $this->setRemoteConectionConfig();
		
		$totalimport = 0;
		$storeId = Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_website',Mage::app()->getStore());
		
		try {			
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
			$writeConnection = $resource->getConnection('core_write');
			$_connection_remote = Mage::getSingleton('core/resource')->createConnection('oscommerce_conection', 'pdo_mysql', $_config);	
			
			$storeId = Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_website',Mage::app()->getStore());

			$sql = "select * from ezmage_customers where customers_imported<>'y'";
			$results = $readConnection->fetchAll($sql);

			foreach($results as $row) {
					//print $row['osc_customers_id'].'<br>';				
					$query = 'select * from customers where customers_id = '.$row['osc_customers_id'];
					$customer_results  = $_connection_remote->fetchAll($query);			
					if (sizeof($customer_results) == 1){
						$customer = Mage::getModel('customer/customer');
						$password = rand(1111111,9999999);
						$customer->setWebsiteId($storeId);
						$customer->loadByEmail($customer_results[0]['']);
						if(!$customer->getId()) {							
								//$groups = Mage::getResourceModel('customer/group_collection')->getData();
								//$groupID = '3';			
								//$customer->setData( 'group_id', $groupID );
								
								$customer->setEmail($customer_results[0]['customers_email_address']);
								$customer->setFirstname($customer_results[0]['customers_firstname']);
								$customer->setLastname($customer_results[0]['customers_lastname']);
								if ($customer_results[0]['customers_lastname'] == 'm'){
									$customer->setGender(1);  // 1 male 2 female
								}else{
									$customer->setGender(2);  // 1 male 2 female
								}
								$customer->setPassword($password);
								$customer->setDob($customer_results[0]['customers_dob']);
				
								$customer->setConfirmation(null);
								$customer->save();
				
								//print $customer->getId().'<br>';
								
								// Get Address
								$query = "SELECT address_book.*,countries.countries_iso_code_2,zones.zone_name FROM (address_book LEFT JOIN countries on countries.countries_id=address_book.entry_country_id)  LEFT JOIN zones on zones.zone_id=address_book.entry_zone_id WHERE address_book.customers_id =".$row['osc_customers_id'];
								$customer_address_book  = $_connection_remote->fetchAll($query);	
								foreach($customer_address_book as $row_address_book) {
									$address = Mage::getModel("customer/address");
									$address->setCustomerId($customer->getId());
									$address->firstname = $row_address_book['entry_firstname'];
									$address->lastname = $row_address_book['entry_lastname'];
									$address->country_id = $row_address_book['countries_iso_code_2'];
									$address->street = $row_address_book['entry_street_address'];
									$address->postcode = $row_address_book['entry_postcode'];
									$address->city = $row_address_book['entry_city'];
									$address->region = $row_address_book['zone_name'];							
									$address->telephone = $customer_results[0]['customers_telephone'];
									$address->company = $row_address_book['entry_company'];
									$address->save();
									
									if ($customer_results[0]['customers_default_address_id'] == $row_address_book['address_book_id']){
										$customers_default_address_id = $address->getId();
									}
								}																							
								$customer->setDefaultBilling($customers_default_address_id);
								$customer->setDefaultShipping($customers_default_address_id);
								$customer->save();								
						}
					}				
					
					// update tmp table
					$sql = "update ezmage_customers set customers_imported='y',mage_customers_id=".$customer->getId()." where osc_customers_id=".$row['osc_customers_id'];		
					$writeConnection->query($sql);	
					
					$totalimport++;
					if ($totalimport == Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_totalperimportcustomer',Mage::app()->getStore())){
						break;
					}	
			}  
									
			//exit;
						
			// get import status							
			$sql = "select osc_customers_id from ezmage_customers where customers_imported='y'";
			$list = $readConnection->fetchAll($sql);
			$importedCustomersTotal = sizeof($list);		
			Mage::getSingleton('core/session')->setimportedCustomersTotal($importedCustomersTotal);	
												
		}
		catch (Exception $ex) {
			//echo $ex->getMessage();
			Mage::getSingleton('core/session')->setErrorMSG('osCommerce Customer ID: '.$customer_row['osc_cat_id'].'<br>Email: '.$customer_results[0]['customers_email_address'].'<br>Exception: '.$ex->getMessage());										
		}										
				
		// Start Indexes		
		foreach ($pCollection as $process) {
		  $process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
		}
													
		$this->loadLayout();
		
    	$block = $this->getLayout()->createBlock(
		    'Mage_Core_Block_Template',
		    'block_ezmage_oscommerce_import_step6',
			array('template' => 'ezmage/oscommerceimport/step6.phtml')
		);
    	
    $this->getLayout()->getBlock('content')->append($block);			
		$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
					
		$this->renderLayout();		
    }	


	/*******************************************************************************/
	/* Step 7																				 														   */
	/*******************************************************************************/
    public function step7Action()
    {																
		
		Mage::getSingleton('core/session')->setErrorMSG('');	
			
		try {			
			//create products top import delete products, shipping, coupon, taxes,others
			$this->CreateGeneralProducts();
			
			
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
										
			$sql = "select osc_order_id from ezmage_orders where order_imported='y'";
			$list = $readConnection->fetchAll($sql);
			$importedOrdersTotal = sizeof($list);		
			Mage::getSingleton('core/session')->setimportedOrdersTotal($importedOrdersTotal);											
		}
		catch (Exception $ex) {
			//Mage::register('conection_status',$ex->getMessage());
			echo $ex->getMessage();
		}	
					
			$this->loadLayout();
			
				$block = $this->getLayout()->createBlock(
					'Mage_Core_Block_Template',
					'block_ezmage_oscommerce_import_step7',
				array('template' => 'ezmage/oscommerceimport/step7.phtml')
			);
				
			$this->getLayout()->getBlock('content')->append($block);			
			$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
						
			$this->renderLayout();
    }	
		
	/*******************************************************************************/
	/* Step 71																			 														   */
	/*******************************************************************************/
    public function step71Action()
    {		
																							
		// Add here another order total methods
		// Discount Modules list
		$discount_modules = array('ot_customer_discount','ot_discount_coupon','ot_giftcard','ot_loyalty_discount','ot_quantity_discount');
		// Other Charges Modules list
		$others_modules = array('ot_custom','ot_loworderfee');

		//$osc_order_totals_store = $this->GetOrderTotalsFromOSCAll($_connection_remote);

		
		// Stop Indexes     
		// http://www.clounce.com/magento/magento-reindex-programmatically
		// http://stackoverflow.com/questions/5420552/magento-programmatically-disable-automatic-indexing
		$pCollection = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
		foreach ($pCollection as $process) {
			$process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
		}
		
		$_config = $this->setRemoteConectionConfig();
		
		$totalimport = 0;
		$storeId = Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_website',Mage::app()->getStore());
			
		try {			
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
			$writeConnection = $resource->getConnection('core_write');
			$_connection_remote = Mage::getSingleton('core/resource')->createConnection('oscommerce_conection', 'pdo_mysql', $_config);	
			
			$sql = "select * from ezmage_orders where order_imported<>'y'";
			$results = $readConnection->fetchAll($sql);
			
			// Get List of ISO Countries
			$country_list = $this->GetCountryList($_connection_remote);
			
			// Import each order
			foreach($results as $row) {
				// GET Order Information
				$osc_order = $this->GetOrderFromOSC($row['osc_order_id'],$_connection_remote);
				// Get Orders products
				$osc_order_products = $this->GetOrderProductsFromOSC($row['osc_order_id'],$_connection_remote);
				// GET Comments Information
				$osc_order_comments = $this->GetCommentsFromOSC($row['osc_order_id'],$_connection_remote);
				// GET Order Totals
				$osc_order_totals = $this->GetOrderTotalsFromOSC($row['osc_order_id'],$_connection_remote);
				
				// Get Shipping Information
				$shipping = $this->GetShippingTitle($osc_order_totals);
																
				// Create Quote Order
				$customer = Mage::getModel('customer/customer');
				$customer->setWebsiteId($storeId);
				$customer->loadByEmail($osc_order['customers_email_address']);
								
				$shopping_cart = $this->PrepareShoppingCart($osc_order_products,$_connection_remote,$osc_order_totals,$discount_modules,$others_modules);										
																
				$params = array("AccountNo" => $customer->getId(), "PartCart" => $shopping_cart);
				$this->global_shopping_cart = $shopping_cart;
				$quote_id = $this->PrepareOrder($params,$osc_order,$osc_order_products,$shopping_cart);
				
				// Create order
				$this->global_shopping_cart = $shopping_cart;				
				$order_id = $this->ConfirmOrder($quote_id,$osc_order_products,$osc_order_totals,$osc_order,$shopping_cart,$shipping);
				
				// Add Comments
				$this->AddOrderComments($order_id,$osc_order_comments);
			
				// Close Order 
				$this->CloseOrder($order_id);
				
				// update ezmage_orders
				$sql = "update ezmage_orders set order_imported='y',mage_order_id=".$order_id." where osc_order_id=".$row['osc_order_id'];		
				$writeConnection->query($sql);	
				
				$totalimport++;
				if ($totalimport == Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_totalperimportorder',Mage::app()->getStore())){
					break;
				}	
											
			}
																							
			// get import status	
			$sql = "select osc_order_id from ezmage_orders where order_imported='y'";
			$list = $readConnection->fetchAll($sql);
			$importedOrdersTotal = sizeof($list);		
			Mage::getSingleton('core/session')->setimportedOrdersTotal($importedOrdersTotal);
			
			// clean table sales_flat_quote if not we can't delete a product
			$sql = "DELETE FROM sales_flat_quote WHERE customer_is_guest = 0";
			$writeConnection->query($sql);
													
		}
		catch (Exception $ex) {
			Mage::register('conection_status','Oscommerce Order ID:'.$row['osc_order_id'].' - '.$ex->getMessage());
			//echo $ex->getMessage();
		}	
					
			$this->loadLayout();
			
				$block = $this->getLayout()->createBlock(
					'Mage_Core_Block_Template',
					'block_ezmage_oscommerce_import_step7',
				array('template' => 'ezmage/oscommerceimport/step7.phtml')
			);
				
			$this->getLayout()->getBlock('content')->append($block);			
			$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
						
			$this->renderLayout();
    }	
		
				
	/*******************************************************************************/
	/* Step 8																				 														   */
	/*******************************************************************************/
    public function step8Action()
    {																
			$this->loadLayout();
			
				$block = $this->getLayout()->createBlock(
					'Mage_Core_Block_Template',
					'block_ezmage_oscommerce_import_step4',
				array('template' => 'ezmage/oscommerceimport/step8.phtml')
			);
				
			$this->getLayout()->getBlock('content')->append($block);			
			$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
						
			$this->renderLayout();
    }	

	/*******************************************************************************/
	/* Step 8.1																			 														   */
	/*******************************************************************************/
    public function step81Action()
    {										
			
			try{
				// check if tmp are created
				$resource = Mage::getSingleton('core/resource');
				$writeConnection = $resource->getConnection('core_write');
				// Remove tmp tables
				$query = 'drop table ezmage_categories';
				$writeConnection->query($query);
				$query = 'drop table ezmage_log';
				$writeConnection->query($query);
				$query = 'drop table ezmage_products';
				$writeConnection->query($query);
				$query = 'drop table ezmage_customers';
				$writeConnection->query($query);
				$query = 'drop table ezmage_orders';
				$writeConnection->query($query);				
						
			}		
			catch (Exception $ex) {			
				echo $ex->getMessage();
			}										
					
			// unregister variables
			Mage::getSingleton('core/session')->unsstep2Status();
			Mage::getSingleton('core/session')->unsimportCategoryTotal();
			Mage::getSingleton('core/session')->unsimportProductsTotal();
			Mage::getSingleton('core/session')->unsimportCategoryTotal();
			Mage::getSingleton('core/session')->unsimportedCategoryTotal();
			Mage::getSingleton('core/session')->unsimportProductsTotal();
			Mage::getSingleton('core/session')->unsimportedProductsTotal();
			Mage::getSingleton('core/session')->unsimportCustomersTotal();
			Mage::getSingleton('core/session')->unsimportedCustomersTotal();
			Mage::getSingleton('core/session')->unsimportOrdersTotal();
			Mage::getSingleton('core/session')->unsimportedOrdersTotal();						
						
			$this->loadLayout();
			
				$block = $this->getLayout()->createBlock(
					'Mage_Core_Block_Template',
					'block_ezmage_oscommerce_import_step4',
				array('template' => 'ezmage/oscommerceimport/step6.phtml')
			);
				
			$this->getLayout()->getBlock('content')->append($block);			
			$this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');
						
			$this->renderLayout();
    }	
	
			
	/***************************************************************************************************************/
	// ORDERS FUNCTIONS 
	/***************************************************************************************************************/

	public function CreateDeleteProduct($product_info){
						
		$parent_category = Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_category',Mage::app()->getStore());
		$storeId = Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_website',Mage::app()->getStore());
		$AttributeSetId = Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_attribute',Mage::app()->getStore());
		
		if ($product_info['products_model'] != ''){
			$sku = $product_info['products_model'];
		}else{
			$sku = $product_info['products_id'];
		}
												
		$product = new Mage_Catalog_Model_Product();				 
		// Build the product
		$product->setSku($sku);
		$product->setAttributeSetId($AttributeSetId); 
		$product->setTypeId('simple');
		$product->setName($product_info['products_name']);
		$product->setCategoryIds($parent_category); 
		$product->setWebsiteIDs(array($storeId)); 
		$product->setDescription('-');
		$product->setShortDescription('-');
		$product->setPrice($product_info['products_price']);  			 
		$product->setWeight(0);
		
		$product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
		$product->setStatus(1);
		$product->setTaxClassId(0); # My default tax class
		$product->setStockData(array(
		'is_in_stock' => 1,
		'qty' => 10000
		));
		
		$product->setCreatedAt(strtotime('now'));																		 
		$product->save();													
															
		return $product->getId();
	}
	
	public function GetExtraChargeName($product_id){				
		$products_title = '';		
		$found = false;
		
		foreach($this->global_shopping_cart as $row) {
			if (($row['PartId'] == $product_id) and ($row['item_type'] == 'osc-extra') and ($found == false)){
				$found = true;
				$products_title = $row['products_title'];;
				if (substr(trim($row['products_title']), -1) == ":"){
					$products_title = substr(trim($row['products_title']), 0, -1);
				}											
			}else{
					$new_shopping_cart[] = $row;
			}						
		}
		
		$this->global_shopping_cart = $new_shopping_cart;
							
		return $products_title;
	}
	
	public function GetProductPrice($product_id){	
		$found = false;
		$price = 0;		
		foreach($this->global_shopping_cart as $row) {
			if (($row['PartId'] == $product_id) and ($found == false)){
					$found = true;
					$price = $row['products_price'];					
			}else{
					$new_shopping_cart[] = $row;
			}						
		}							
		$this->global_shopping_cart = $new_shopping_cart;
		return $price;		
	}

	public function CloseOrder($order_id){
		$order = Mage::getModel('sales/order')->loadByIncrementID($order_id);
		$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
		$order->save();
		
		try {
			if(!$order->canInvoice()){
				Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));				
			}
		
		$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
		
		if (!$invoice->getTotalQty()) {
			Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
		}
		
		$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
		$invoice->register();
		$transactionSave = Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder());
		
		$transactionSave->save();
		}
		catch (Mage_Core_Exception $e) {
			Mage::register('conection_status','Invoice - Order ID:'.order_id.' - '.$e->getMessage());
		}
		
		try {
		if($order->canShip()) {
			$itemQty =  $order->getItemsCollection()->count();
			$shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($itemQty);
			$shipment = new Mage_Sales_Model_Order_Shipment_Api();
			$shipmentId = $shipment->create($order_id);	
		}
		}catch (Mage_Core_Exception $e) {
			Mage::register('conection_status','Shipping - Order ID:'.order_id.' - '.$e->getMessage());
		}

	}


	public function AddOrderComments($order_id,$osc_order_comments){
		foreach($osc_order_comments as $row) {			
			// Get the order - we could also use the internal Magento order ID and the load() method here
			$order = Mage::getModel('sales/order')->loadByIncrementId($order_id);			
			// Add the comment and save the order
			$comment = $row['orders_status_name'];
			if ($row['comments'] != ''){
				$comment = $comment.' - '.$row['comments'];
			}
			$is_customer_notified = false;
			if ($row['customer_notified'] == 1){
				$is_customer_notified = true;			
			}			
			$order->addStatusToHistory($order->getStatus(), $comment, $is_customer_notified);
			$order->save();
		}
	}

	public function ConfirmOrder($quoteId,$osc_order_products,$osc_order_totals,$osc_order,$shopping_cart,$shipping){
				
    $hpc_payment_method = 'purchaseorder'; // checkmo purchaseorder		
    //methods: flatrate_flatrate, freeshipping_freeshipping
    $hpc_shipping_method = 'flatrate_flatrate';
    $hpc_shipping_method_description = $shipping;

    $quoteObj = Mage::getModel('sales/quote')->load($quoteId);
		
    $items = $quoteObj->getAllItems();
  			
	  $quoteObj->collectTotals();
    $quoteObj->reserveOrderId();
		
    $quotePaymentObj = $quoteObj->getPayment();
    //methods: authorizenet, paypal_express, googlecheckout, purchaseorder
    $quotePaymentObj->setMethod($hpc_payment_method);

    $quoteObj->setPayment($quotePaymentObj);
    $convertQuoteObj = Mage::getSingleton('sales/convert_quote');

    $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getShippingAddress());

    $orderPaymentObj = $convertQuoteObj->paymentToOrderPayment($quotePaymentObj);
    $orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getBillingAddress()));

    $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getShippingAddress()))
            ->setShipping_method($hpc_shipping_method)
            ->setShippingDescription($hpc_shipping_method_description);

    $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
							
    foreach ($items as $item) {
        //@var $item Mage_Sales_Model_Quote_Item
        $orderItem = $convertQuoteObj->itemToOrderItem($item);
        if ($item->getParentItem()) {
            $orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
        }
												
				$product_name = $this->GetExtraChargeName($orderItem->getProductId());
				if ($product_name != ''){
					$orderItem->setName($product_name);
				}				

				//$orderItem->setOriginalPrice($this->GetProductPrice($orderItem->getId(),$shopping_cart));																							
        $orderObj->addItem($orderItem);												
    }
		
    $orderObj->setCanShipPartiallyItem(false);

    $totalDue = $orderObj->getTotalDue();

    //$orderObj->sendNewOrderEmail();		
		$orderObj->setCreatedAt($osc_order['date_purchased']); 
		// 2012-06-13 17:50:18
		//$a = strptime($osc_order['date_purchased'], '%Y-%m-%d %H:%M:%S');
		//$timestamp = mktime($a['tm_hour'], $a['tm_min'], $a['tm_sec'], $a['tm_mon'], $a['tm_mday'], $a['tm_year']);
		//$timestamp = mktime(17,17,17, 06, 13, 2012);
		//$orderObj->setCreatedAt(date('Y-m-d h:i:s A', $timestamp)); 

    $orderObj->place(); //calls _placePayment
    $orderObj->save();

    $orderObj->load(Mage::getSingleton('sales/order')->getLastOrderId());
    $lastOrderId = $orderObj->getIncrementId();
		
		// Change status to Proccesing
		$order = Mage::getModel('sales/order')->loadByIncrementID($lastOrderId);
		$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
		$order->save();		
		
    return $lastOrderId;				
	}

	public function PrepareOrder($params,$osc_order,$osc_order_products,$shopping_cart){				
    foreach ($params as $k => $v) {
        $$k = $v;
    }
    $customerObj = Mage::getModel('customer/customer')->load($AccountNo);
    $storeId = $customerObj->getStoreId();
    $quoteObj = Mage::getModel('sales/quote')->assignCustomer($customerObj); //sets ship/bill address
    $storeObj = $quoteObj->getStore()->load($storeId);
    $quoteObj->setStore($storeObj);
    $productModel = Mage::getModel('catalog/product');

    foreach ($PartCart as $part) {
        foreach ($part as $k => $v) {
            $$k = $v;
        }
        $productObj = $productModel->load($PartId);

        try {
            $quoteItem = Mage::getModel('sales/quote_item')->setProduct($productObj);												
        } catch (Exception $e) {
            Mage::register('conection_status','quote_item :'.$e->getMessage());
        }
        $quoteItem->setQuote($quoteObj);
        $quoteItem->setQty($Quantity);
				

				$quoteObj->setBillingAddress($this->GetOrderAddress($osc_order,'billing'));
				$quoteObj->setShippingAddress($this->GetOrderAddress($osc_order,'shipping'));
				
				$quoteItem->setOriginalCustomPrice($this->GetProductPrice($PartId));				
        $quoteObj->addItem($quoteItem);
    }
						
    //$shippingMethod = 'flatrate_flatrate';
    //$quoteObj->getShippingAddress()->setShippingMethod($shippingMethod);
    //$quoteObj->getShippingAddress()->setCollectShippingRates(true);
    //$quoteObj->getShippingAddress()->collectShippingRates();
    //$quoteObj->collectTotals(); // calls $address->collectTotals();
		
    $quoteObj->save();
    $quoteId = $quoteObj->getId();				
    return $quoteId;		
	}

	// Return Address of the Order
	public function GetOrderAddress($osc_order,$type,$country_list){
		$address = Mage::getModel('sales/quote_address');
		if ($type == 'billing'){
			$name = explode(" ",$osc_order['billing_name']);			
			$address['firstname'] = $name[0];
			$address['lastname'] = $name[1];
			$address['company'] = $osc_order['billing_company'];
			$address['email'] = $osc_order['customers_email_address'];
			$address['street'] = $osc_order['billing_street_address'];
			$address['city'] = $osc_order['billing_city'];
			$address['postcode'] = $osc_order['billing_postcode'];
			$address['country_id'] = $this->GetCountryISO2($osc_order['billing_country']);		//countries_iso_code_2
			$address['telephone'] = $osc_order['customers_telephone'];
			$address['save_in_address_book'] = 0;																					
		}else{
			$name = explode(" ",$osc_order['delivery_name']);			
			$address['firstname'] = $name[0];
			$address['lastname'] = $name[1];
			$address['company'] = $osc_order['delivery_company'];
			$address['email'] = $osc_order['customers_email_address'];
			$address['street'] = $osc_order['delivery_street_address'];
			$address['city'] = $osc_order['delivery_city'];
			$address['postcode'] = $osc_order['delivery_postcode'];
			$address['country_id'] = $this->GetCountryISO2($osc_order['delivery_country'],$country_list);		//countries_iso_code_2
			$address['telephone'] = $osc_order['customers_telephone'];
			$address['save_in_address_book'] = 0;			
		}
		return $address;
	}

	public function GetCountryISO2($country,$country_list){		
		foreach($country_list as $country_name => $country_iso) {
			if ($country_name == $country){
				return $country_iso;
				break;
			}
		}
	}

	public function GetCountryList($_connection_remote){
		$query = 'SELECT countries_name,countries_iso_code_2 from countries';
		$results  = $_connection_remote->fetchAll($query);	
		foreach($results as $row) {
			$country_list[$row['countries_name']] = $row['countries_iso_code_2'];
		}		
		return $country_list;
	}	

	public function GetShippingTitle($osc_order_totals){
		$shippinh_title = '';
		foreach($osc_order_totals as $row) {
			if ($row['class'] == 'ot_shipping'){
				$shippinh_title = $row['title'];
				if (substr(trim($row['title']), -1) == ":"){
					$shippinh_title = substr(trim($row['title']), 0, -1);
				}				
				break;
			}			
		}					
		return $shippinh_title;
	}

	public function PrepareShoppingCart($osc_order_products,$_connection_remote,$osc_order_totals,$discount_modules,$others_modules){
		foreach($osc_order_products as $row) {			
			if ($row['products_model'] != ''){
				$sku = $row['products_model'];
			}else{
				$sku = $row['products_id'];
			}
			// Get Product ID
			$oProduct = Mage::getModel("catalog/product")
                ->getCollection()
                ->setStoreId($storeId)
                ->addAttributeToSelect("sku")
                ->addFieldToFilter("sku", array('eq' => $sku))                        
                ->getFirstItem();
			
			if ( sizeof($oProduct->getData()) > 0){
				$shopping_cart[] = array("PartId" => $oProduct->getId(), 
																	"Quantity" => $row['products_quantity'], 
																	"products_id" => $row['products_id'], 
																	"products_price" =>  $row['products_price'],
																	"products_title" => '',
																	"item_type" => 'product');
			}else{
				// Get Product ID
				$product_Id = $this->CreateDeleteProduct($row);
				$shopping_cart[] = array("PartId" => $product_Id, 
																	"Quantity" => $row['products_quantity'], 
																	"products_id" => $row['products_id'], 
																	"products_price" =>  $row['products_price'],
																	"products_title" => $row['products_name'],
																	"item_type" => 'osc-product');
			}							
		}	
		
		// Add to Shopping Cart -> Shipping Cost, Discount 
		// $osc_order_totals,$discount_modules,$others_modules

		// Get osc-shipping Product ID
		$oProduct = Mage::getModel("catalog/product")
								->getCollection()
								->setStoreId($storeId)
								->addAttributeToSelect("sku")
								->addFieldToFilter("sku", array('eq' => 'osc-shipping'))                        
								->getFirstItem();
		$shipping_id = $oProduct->getId();
		
		// Get osc-discount Product ID
		$oProduct = Mage::getModel("catalog/product")
								->getCollection()
								->setStoreId($storeId)
								->addAttributeToSelect("sku")
								->addFieldToFilter("sku", array('eq' => 'osc-discount'))                        
								->getFirstItem();
		$discount_id = $oProduct->getId();

		// Get osc-tax Product ID
		$oProduct = Mage::getModel("catalog/product")
								->getCollection()
								->setStoreId($storeId)
								->addAttributeToSelect("sku")
								->addFieldToFilter("sku", array('eq' => 'osc-tax'))                        
								->getFirstItem();
		$tax_id = $oProduct->getId();
		
		// Get osc-other Product ID
		$oProduct = Mage::getModel("catalog/product")
								->getCollection()
								->setStoreId($storeId)
								->addAttributeToSelect("sku")
								->addFieldToFilter("sku", array('eq' => 'osc-other'))                        
								->getFirstItem();
		$other_id = $oProduct->getId();		
		
		foreach($osc_order_totals as $row) {
				if (in_array($row['class'], $discount_modules)){
					$shopping_cart[] = array("PartId" => $discount_id, 
																	"Quantity" => 1, 
																	"products_id" => $row['orders_total_id'], 
																	"products_price" =>  $row['value'],
																	"products_title" => $row['title'],
																	"item_type" => 'osc-extra');
																	
				}elseif ($row['class'] == 'ot_tax' ){
					$shopping_cart[] = array("PartId" => $tax_id, 
																	"Quantity" => 1, 
																	"products_id" => $row['orders_total_id'], 
																	"products_price" =>  $row['value'],
																	"products_title" => $row['title'],
																	"item_type" => 'osc-extra');
					
				}elseif ($row['class'] == 'ot_shipping' ){
					$shopping_cart[] = array("PartId" => $shipping_id, 
																	"Quantity" => 1, 
																	"products_id" => $row['orders_total_id'], 
																	"products_price" =>  $row['value'],
																	"products_title" => $row['title'],
																	"item_type" => 'osc-extra');					
				}else{
					$shopping_cart[] = array("PartId" => $other_id, 
																	"Quantity" => 1, 
																	"products_id" => $row['orders_total_id'], 
																	"products_price" =>  $row['value'],
																	"products_title" => $row['title'],
																	"item_type" => 'osc-extra');					
				}
		}
															
		return $shopping_cart;
	}

	// GET Order Information
	public function GetOrderFromOSC($order_id,$_connection_remote){
		$query = 'select * from orders where  orders_id = '.$order_id;
		$results  = $_connection_remote->fetchAll($query);			
		if (sizeof($results) == 1){
			$product = $results[0];
		}else{
			$product['products_id'] = 0;
		}			
		return $product;			
	}

	// GET Order Products Information
	public function GetOrderProductsFromOSC($order_id,$_connection_remote){	
		$query = 'SELECT * from orders_products where orders_id='.$order_id;
		$results  = $_connection_remote->fetchAll($query);	
		foreach($results as $row) {
			$order_products[] = $row;
		}							
		return $order_products;
	}
	
	// GET Orders Comments Information
	public function GetCommentsFromOSC($order_id,$_connection_remote){	
		$query = 'SELECT orders_status_history . * , orders_status.orders_status_name FROM orders_status_history, orders_status where orders_status.orders_status_id = orders_status_history.orders_status_id AND orders_status.language_id =1 AND orders_status_history.orders_id ='.$order_id;
		$results  = $_connection_remote->fetchAll($query);	
		foreach($results as $row) {
			$order_comments[] = $row;
		}				
		return $order_comments;
	}

	// GET Order Totals Store
	public function GetOrderTotalsFromOSCAll($_connection_remote){
		$query = 'SELECT DISTINCT * FROM `orders_total` WHERE class <>"ot_total" and class<>"ot_subtotal"';
		$results  = $_connection_remote->fetchAll($query);	
		foreach($results as $row) {
			$order_totals[] = $row;
		}							
		return $order_totals;	
	}
	
	// GET Order Totals Per Order
	public function GetOrderTotalsFromOSC($order_id,$_connection_remote){
		$query = 'SELECT * FROM `orders_total` WHERE class <>"ot_total" and class<>"ot_subtotal" and `orders_id`='.$order_id;
		$results  = $_connection_remote->fetchAll($query);	
		foreach($results as $row) {
			$order_totals[] = $row;
		}							
		return $order_totals;	
	}
				

	public function CreateGeneralProducts(){
		try {
			
				$parent_category = Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_category',Mage::app()->getStore());
				$storeId = Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_website',Mage::app()->getStore());
				$AttributeSetId = Mage::getStoreConfig('oscommerceimportconf/mageconfiguration/conf_attribute',Mage::app()->getStore());
											
				/////////////////////////////////////////////////////////////////////////////////////
				// Create Custom Product for Import Shipping
				/////////////////////////////////////////////////////////////////////////////////////
								
				$pro_Model = 'osc-shipping';
				$pro_Name = 'Shipping';
				$oProduct = Mage::getModel("catalog/product")
                ->getCollection()
                ->setStoreId($storeId)
                ->addAttributeToSelect("sku")
                ->addFieldToFilter("sku", array('eq' => $pro_Model))                        
                ->getFirstItem();
				
				
				if ( sizeof($oProduct->getData()) == 0){
					$product = new Mage_Catalog_Model_Product();				 
					// Build the product
					$product->setSku($pro_Model);
					$product->setAttributeSetId($AttributeSetId); 
					$product->setTypeId('simple');
					$product->setName($pro_Name);
					$product->setCategoryIds($parent_category); 
					$product->setWebsiteIDs(array($storeId)); 
					$product->setDescription('-');
					$product->setShortDescription('-');
					$product->setPrice(0);  			 
					$product->setWeight(0);
					
					$product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
					$product->setStatus(1);
					$product->setTaxClassId(0); # My default tax class
					$product->setStockData(array(
					'is_in_stock' => 1,
					'qty' => 10000
					));
					
					$product->setCreatedAt(strtotime('now'));																		 
					$product->save();										
				}				

				/////////////////////////////////////////////////////////////////////////////////////
				// Create Custom Product for Import Discount
				/////////////////////////////////////////////////////////////////////////////////////
								
				$pro_Model = 'osc-discount';
				$pro_Name = 'Discount';
				$oProduct = Mage::getModel("catalog/product")
                ->getCollection()
                ->setStoreId($storeId)
                ->addAttributeToSelect("sku")
                ->addFieldToFilter("sku", array('eq' => $pro_Model))                        
                ->getFirstItem();
				
				
				if ( sizeof($oProduct->getData()) == 0){
					$product = new Mage_Catalog_Model_Product();				 
					// Build the product
					$product->setSku($pro_Model);
					$product->setAttributeSetId($AttributeSetId); 
					$product->setTypeId('simple');
					$product->setName($pro_Name);
					$product->setCategoryIds($parent_category); 
					$product->setWebsiteIDs(array($storeId)); 
					$product->setDescription('-');
					$product->setShortDescription('-');
					$product->setPrice(0);  			 
					$product->setWeight(0);
					
					$product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
					$product->setStatus(1);
					$product->setTaxClassId(0); # My default tax class
					$product->setStockData(array(
					'is_in_stock' => 1,
					'qty' => 10000
					));
					
					$product->setCreatedAt(strtotime('now'));																		 
					$product->save();										
				}				

				/////////////////////////////////////////////////////////////////////////////////////
				// Create Custom Product for Import Taxes
				/////////////////////////////////////////////////////////////////////////////////////
								
				$pro_Model = 'osc-tax';
				$pro_Name = 'Tax';
				$oProduct = Mage::getModel("catalog/product")
                ->getCollection()
                ->setStoreId($storeId)
                ->addAttributeToSelect("sku")
                ->addFieldToFilter("sku", array('eq' => $pro_Model))                        
                ->getFirstItem();
				
				
				if ( sizeof($oProduct->getData()) == 0){
					$product = new Mage_Catalog_Model_Product();				 
					// Build the product
					$product->setSku($pro_Model);
					$product->setAttributeSetId($AttributeSetId); 
					$product->setTypeId('simple');
					$product->setName($pro_Name);
					$product->setCategoryIds($parent_category); 
					$product->setWebsiteIDs(array($storeId)); 
					$product->setDescription('-');
					$product->setShortDescription('-');
					$product->setPrice(0);  			 
					$product->setWeight(0);
					
					$product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
					$product->setStatus(1);
					$product->setTaxClassId(0); # My default tax class
					$product->setStockData(array(
					'is_in_stock' => 1,
					'qty' => 10000
					));
					
					$product->setCreatedAt(strtotime('now'));																		 
					$product->save();										
				}				

				/////////////////////////////////////////////////////////////////////////////////////
				// Create Custom Product for Import Other
				/////////////////////////////////////////////////////////////////////////////////////
								
				$pro_Model = 'osc-other';
				$pro_Name = 'Other Charges';
				$oProduct = Mage::getModel("catalog/product")
                ->getCollection()
                ->setStoreId($storeId)
                ->addAttributeToSelect("sku")
                ->addFieldToFilter("sku", array('eq' => $pro_Model))                        
                ->getFirstItem();
				
				
				if ( sizeof($oProduct->getData()) == 0){
					$product = new Mage_Catalog_Model_Product();				 
					// Build the product
					$product->setSku($pro_Model);
					$product->setAttributeSetId($AttributeSetId); 
					$product->setTypeId('simple');
					$product->setName($pro_Name);
					$product->setCategoryIds($parent_category); 
					$product->setWebsiteIDs(array($storeId)); 
					$product->setDescription('-');
					$product->setShortDescription('-');
					$product->setPrice(1);  			 
					$product->setWeight(0);
					
					$product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
					$product->setStatus(1);
					$product->setTaxClassId(0); # My default tax class
					$product->setStockData(array(
					'is_in_stock' => 1,
					'qty' => 10000
					));
					
					$product->setCreatedAt(strtotime('now'));																		 
					$product->save();										
				}				

				
		}catch (Exception $ex) {
			//echo $ex->getMessage();
			Mage::getSingleton('core/session')->setErrorMSG('Creating Import Products: '.$pro_Name.' - '.$ex->getMessage());										
		}				
	}

	/***************************************************************************************************************/
	// Utils 
	/***************************************************************************************************************/


	// check image file extension
	public function getImageAllow($file){
		$path = pathinfo($file, PATHINFO_EXTENSION);		
		if ( (strtolower($path) == 'jpg' ) or (strtolower($path) == 'gif' ) or (strtolower($path) == 'png' ) ){
			return true;
		}else{
			return false;
		}		
	}
	
	// Get product Information from oscommerce
	public function getProductFromOSC($osc_product_id,$readConnection,$_connection_remote,$conf_language_id){
			$query = 'select products_description.*,products.* from products left join products_description on products.products_id=products_description.products_id where  products.products_id = '.$osc_product_id.' and  products_description.language_id = '.$conf_language_id;
			$results  = $_connection_remote->fetchAll($query);			
			if (sizeof($results) == 1){
				$product = $results[0];
			}else{
				$product['products_id'] = 0;
			}			
		return $product;
	}
	
	// Returns Mage Categories			
	public function getCategoriesProductFromOSC($osc_product_id,$readConnection,$_connection_remote,$parent_cat){
		$query = 'select categories_id from products_to_categories where products_id = '.$osc_product_id;
		$results  = $_connection_remote->fetchAll($query);	
		foreach($results as $row) {
			// find magento category id 			
		 $sql = "select mage_cat_id from ezmage_categories where osc_cat_id=".$row['categories_id'];
		 $mage_cat_id = $readConnection->fetchOne($sql);			
		 if ($mage_cat_id > 0){
		 		$product_categories[] = $mage_cat_id;
		 }
		}					
		if (sizeof($product_categories) == 0){
			$product_categories[] = $parent_cat;
		}				 
		return $product_categories;
	}
				
				
	// Download Image
	public function  getDownloadImage($type,$file){
		$path = str_replace("index.php","",$_SERVER["SCRIPT_FILENAME"]);		
		$import_location = $path.'media/catalog/';
		if (!file_exists($import_location)){
			mkdir($import_location, 0755);
		}
		$import_location = $path.'media/catalog/'.$type.'/';
		if (!file_exists($import_location)){
			mkdir($import_location, 0755);
		}
		
		// todo check if last character has /
		
		$file_source = Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_imageurl',Mage::app()->getStore()).$file;
		$file_target = $import_location."/".basename($file);
						
		$file_path = "";
		if (($file != '') and (!file_exists($file_target))){
			$rh = fopen($file_source, 'rb');
			$wh = fopen($file_target, 'wb');
			if ($rh===false || $wh===false) {
				// error reading or opening file
				$file_path = "";
			}
			while (!feof($rh)) {
				if (fwrite($wh, fread($rh, 1024)) === FALSE) {
					$file_path = $file_target;
				}
			}
			fclose($rh);
			fclose($wh);
		}
		if (file_exists($file_target)){
			if ($type == 'category'){
				$file_path = $file;
			}else{
				$file_path = $file_target;
			}			
		}
				
		return $file_path;
	}
	
	// Generate mage parent id
	public function getMageParentID($osc_cat_parent,$readConnection) {		
		$sql = "select mage_cat_id from ezmage_categories where osc_cat_id=".(int)$osc_cat_parent;
		$mage_cat_id = $readConnection->fetchOne($sql);
		return $mage_cat_id;
	}
		
	// Generate category tree
	public function tep_get_subcategories(&$categories_array = '', $parent_id = '0',$readConnection) {
		//$languages_id = $conf_language_id;		
		if (!is_array($categories_array)) $categories_array = array();
		$sql = "select * from ezmage_categories where osc_cat_parent=".(int)$parent_id;
		$results = $readConnection->fetchAll($sql);
		foreach($results as $row) {
			$counter = count($categories_array);
			$categories_array[$counter]['osc_cat_id'] = $row['osc_cat_id'];
			$categories_array[$counter]['osc_cat_title'] = $row['osc_cat_title'];
			$categories_array[$counter]['osc_cat_parent'] = $row['osc_cat_parent'];
			$categories_array[$counter]['osc_cat_image'] = $row['osc_cat_image'];		
			$categories_array[$counter]['cat_imported'] = $row['cat_imported'];
								
			if ($row['osc_cat_id'] != $parent_id) {
				$categories_array = $this->tep_get_subcategories(&$categories_array, $row['osc_cat_id'],$readConnection);
	  		}
			
		}
		return $categories_array;
	}
  
  	
	// Save Log
	public function saveException($log_type,$log_desc,$mage_log){					
	}
	
	// Set remote conection
	public function setRemoteConectionConfig(){
	// Conect to remote db
		$_config = array();
		$_config['host'] = Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_hostname',Mage::app()->getStore());
		if (Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_port',Mage::app()->getStore()) != ''){
			$_config['port'] = Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_port',Mage::app()->getStore());
		}
		$_config['dbname'] = Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_db',Mage::app()->getStore());
		$_config['username'] = Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_username',Mage::app()->getStore());
		$_config['password'] = Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_password',Mage::app()->getStore());
		//$_config_prefix 		= Mage::getStoreConfig('oscommerceimportconf/oscconfiguration/conf_prefix',Mage::app()->getStore());						
		// Setting the default Values
		$_config['initStatements'] = 'SET NAMES utf8';
		$_config['model'] = 'mysql4';
		$_config['type'] = 'pdo_mysql';
		$_config['pdoType'] = '';
		$_config['active'] = '1';
		
		return $_config;
	}
		
}