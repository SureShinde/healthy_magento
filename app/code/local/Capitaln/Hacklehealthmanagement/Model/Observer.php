<?php

class Capitaln_Hacklehealthmanagement_Model_Observer
{
    /**
     * Flag to stop observer executing more than once
     *
     * @var static bool
     */
    static protected $_singletonFlag = false;

 
    /**
     * This method will run when the product is saved from the Magento Admin
     * Use this function to update the product model, process the
     * data or anything you like
     *
     * @param Varien_Event_Observer $observer
     */
    public function saveProductTabData(Varien_Event_Observer $observer)
    {
        if (!self::$_singletonFlag) {
            self::$_singletonFlag = true;
            $product = $observer->getEvent()->getProduct();
            try {
                //$customFieldValue =  $this->_getRequest()->getPost('custom_field');
                //echo "<pre>";print_r($this->_getRequest()->getPost()); exit;
                $resource = Mage::getSingleton('core/resource');
                $readConnection = $resource->getConnection('core_read');    
                $writeConnection = $resource->getConnection('core_write');
                if($this->_getRequest()->getPost('subscription_occurance_value') == 1){
                    if(intval($this->_getRequest()->getPost('pID')) ) {
                         if($this->_getRequest()->getPost('chk_shake_1') == 1){// HMR 120
                            $hmr_product_shakes_qty_result = $readConnection->fetchAll('SELECT * FROM custom_hmr_product_shakes WHERE product_id = ' . intval($this->_getRequest()->getPost('pID')) . ' AND shake_id = 1');
                            if(count($hmr_product_shakes_qty_result) > 0 ){
                                $query = "UPDATE custom_hmr_product_shakes SET
                                         total_quantity = '" . intval($this->_getRequest()->getPost('hmr_120_qty')) . "'                                            
                                         WHERE product_id = " . intval($this->_getRequest()->getPost('pID')) . " AND shake_id = 1;";
                                $writeConnection->query($query);
                            }else{
                                $query = "INSERT INTO custom_hmr_product_shakes (product_id, shake_id, total_quantity) VALUES (
                                         " . intval($this->_getRequest()->getPost('pID')) . ", 1, " .intval($this->_getRequest()->getPost('hmr_120_qty')) . ");";
                                $writeConnection->query($query);
                                $shakes_id = "";
                                $no_of_shakes = 0;
                                $current_hmr_product_shakes_result = $readConnection->fetchAll('SELECT * FROM custom_hmr_product_shakes WHERE product_id = ' . intval($this->_getRequest()->getPost('pID'))); 
                                if(count($current_hmr_product_shakes_result) > 0 ){
                                    foreach( $current_hmr_product_shakes_result as $hmr_product_shake) {          
                                      $shakes_id .= $hmr_product_shake['shake_id'].",";
                                      $no_of_shakes = $no_of_shakes + 1;
                                    }
                                }
                                $query = "UPDATE custom_hmr_product_kit SET
                                             no_of_shakes = '". $no_of_shakes . "',
                                             shakes_id = '" . $shakes_id . "'                                           
                                             WHERE product_id = " . intval($this->_getRequest()->getPost('pID')) . ";";                                         
                                $writeConnection->query($query);
                            }
                            // choco flavour
                            $hmr_product_shakes_flavour_values = $readConnection->fetchAll('SELECT * FROM custom_hmr_product_shakes_values WHERE product_id = ' . intval($this->_getRequest()->getPost('pID')) . ' AND shake_id = 1 AND flavour_id = 1');
                            if(count($hmr_product_shakes_flavour_values) >= 0 ){
                                $query_shakeval_cho = "UPDATE custom_hmr_product_shakes_values SET
                                         default_qty = '" . intval($this->_getRequest()->getPost('hmr_120_cho_qty')) . "'                                           
                                         WHERE product_id = " . intval($this->_getRequest()->getPost('pID')) . " AND shake_id = 1 AND flavour_id = 1;";
                                $writeConnection->query($query_shakeval_cho);
                            }else {
                                $query_shakeval_cho = "INSERT INTO custom_hmr_product_shakes_values (product_id, shake_id, flavour_id, default_qty) 
                                VALUES (" . intval($this->_getRequest()->getPost('pID')) . ", 1, 1 ," .intval($this->_getRequest()->getPost('hmr_120_cho_qty')).");";
                                $writeConnection->query($query_shakeval_cho);
                            }
                            // vanila flavour
                            $hmr_product_shakes_flavour__van_values = $readConnection->fetchAll('SELECT * FROM custom_hmr_product_shakes_values WHERE product_id = ' . intval($this->_getRequest()->getPost('pID')) . ' AND shake_id = 1 AND flavour_id = 2');
                            if(count($hmr_product_shakes_flavour__van_values) >= 0 ){
                                $query_shakeval_van = "UPDATE custom_hmr_product_shakes_values SET
                                         default_qty = '" . intval($this->_getRequest()->getPost('hmr_120_van_qty')) . "'                                           
                                         WHERE product_id = " . intval($this->_getRequest()->getPost('pID')) . " AND shake_id = 1 AND flavour_id = 2;";
                                $writeConnection->query($query_shakeval_van);
                            }else {
                                $query_shakeval_cho = "INSERT INTO custom_hmr_product_shakes_values (product_id, shake_id, flavour_id, default_qty) 
                                VALUES (" . intval($this->_getRequest()->getPost('pID')) . ", 1, 2 ," .intval($this->_getRequest()->getPost('hmr_120_van_qty')).");";
                                $writeConnection->query($query_shakeval_cho);
                            }
                         }else{
                            $query = "DELETE FROM custom_hmr_product_shakes WHERE product_id = " . intval($this->_getRequest()->getPost('pID')) . " AND shake_id = 1;";
                            $writeConnection->query($query); 
							$query1 = "update custom_hmr_product_shakes_values set default_qty = 0 WHERE product_id = " . intval($this->_getRequest()->getPost('pID')) . " AND shake_id = 1;";
                            $writeConnection->query($query1);
                            $shakes_id = "";
                            $no_of_shakes = 0;
                            $current_hmr_product_shakes_result = $readConnection->fetchAll('SELECT * FROM custom_hmr_product_shakes WHERE product_id = ' . intval($this->_getRequest()->getPost('pID'))); 
                            if(count($current_hmr_product_shakes_result) > 0 ){
                                foreach( $current_hmr_product_shakes_result as $hmr_product_shake) {          
                                  $shakes_id .= $hmr_product_shake['shake_id'].",";
                                  $no_of_shakes = $no_of_shakes + 1;
                                }
                            }                           
                            $query = "UPDATE custom_hmr_product_kit SET
                                         no_of_shakes = '". $no_of_shakes . "',
                                         shakes_id = '" . $shakes_id . "'                                           
                                         WHERE product_id = " . intval($this->_getRequest()->getPost('pID')) . ";";
                            $writeConnection->query($query);
                         }
                         if($this->_getRequest()->getPost('chk_shake_2') == 2){ // HMR 70
                            $hmr_product_shakes_qty_result = $readConnection->fetchAll('SELECT * FROM custom_hmr_product_shakes WHERE product_id = ' . intval($this->_getRequest()->getPost('pID')) . ' AND shake_id = 2');
                            if(count($hmr_product_shakes_qty_result) > 0 ){
                                $query = "UPDATE custom_hmr_product_shakes SET
                                         total_quantity = '" . intval($this->_getRequest()->getPost('hmr_120_qty')) . "'                                            
                                         WHERE product_id = " . intval($this->_getRequest()->getPost('pID')) . " AND shake_id = 2;";
                                $writeConnection->query($query);
                            }else{
                                
                                $query = "INSERT INTO custom_hmr_product_shakes (product_id, shake_id, total_quantity) VALUES (
                                         " . intval($this->_getRequest()->getPost('pID')) . ", 2, " .intval($this->_getRequest()->getPost('hmr_120_qty')) . ");";
                                $writeConnection->query($query);

                                $shakes_id = "";
                                $no_of_shakes = 0;
                                $current_hmr_product_shakes_result = $readConnection->fetchAll('SELECT * FROM custom_hmr_product_shakes WHERE product_id = ' . intval($this->_getRequest()->getPost('pID'))); 
                                if(count($current_hmr_product_shakes_result) > 0 ){
                                    foreach( $current_hmr_product_shakes_result as $hmr_product_shake) {          
                                      $shakes_id .= $hmr_product_shake['shake_id'].",";
                                      $no_of_shakes = $no_of_shakes + 1;
                                    }
                                }

                                $query = "UPDATE custom_hmr_product_kit SET
                                             no_of_shakes = '". $no_of_shakes . "',
                                             shakes_id = '" . $shakes_id . "'                                           
                                             WHERE product_id = " . intval($this->_getRequest()->getPost('pID')) . ";";
                                //echo $query . "<br>";             
                                $writeConnection->query($query);
                            }

                            // choco flavour
                            $hmr_product_shakes_flavour_values = $readConnection->fetchAll('SELECT * FROM custom_hmr_product_shakes_values WHERE product_id = ' . intval($this->_getRequest()->getPost('pID')) . ' AND shake_id = 2 AND flavour_id = 1');
                            
                            if(count($hmr_product_shakes_flavour_values) >= 0 ){
                                $query_shakeval_cho = "UPDATE custom_hmr_product_shakes_values SET
                                         default_qty = '" . intval($this->_getRequest()->getPost('hmr_70_cho_qty')) . "'                                            
                                         WHERE product_id = " . intval($this->_getRequest()->getPost('pID')) . " AND shake_id = 2 AND flavour_id = 1;";
                                //echo $query . "<br>";             
                                $writeConnection->query($query_shakeval_cho);
                            }else {
                                $query_shakeval_cho = "INSERT INTO custom_hmr_product_shakes_values (product_id, shake_id, flavour_id, default_qty) 
                                VALUES (" . intval($this->_getRequest()->getPost('pID')) . ", 2, 1 ," .intval($this->_getRequest()->getPost('hmr_70_cho_qty')).");";
                                $writeConnection->query($query_shakeval_cho);
                            }
                            // vanila flavour
                            $hmr_product_shakes_flavour_van_values = $readConnection->fetchAll('SELECT * FROM custom_hmr_product_shakes_values WHERE product_id = ' . intval($this->_getRequest()->getPost('pID')) . ' AND shake_id = 2 AND flavour_id = 2');
                            
                            if(count($hmr_product_shakes_flavour_van_values) >= 0 ){
                                $query_shakeval_van = "UPDATE custom_hmr_product_shakes_values SET
                                         default_qty = '" . intval($this->_getRequest()->getPost('hmr_70_van_qty')) . "'                                            
                                         WHERE product_id = " . intval($this->_getRequest()->getPost('pID')) . " AND shake_id = 2 AND flavour_id = 2;";
                                //echo $query . "<br>";             
                                $writeConnection->query($query_shakeval_van);
                            }else {
                                $query_shakeval_van = "INSERT INTO custom_hmr_product_shakes_values (product_id, shake_id, flavour_id, default_qty) 
                                VALUES (" . intval($this->_getRequest()->getPost('pID')) . ", 2, 2 ," .intval($this->_getRequest()->getPost('hmr_70_van_qty')).");";
                                $writeConnection->query($query_shakeval_van);
                            }


                         }else{
                            $query = "DELETE FROM custom_hmr_product_shakes WHERE product_id = " . intval($this->_getRequest()->getPost('pID')) . " AND shake_id = 2;";
                            $writeConnection->query($query);
							 $query1 = "update custom_hmr_product_shakes_values set default_qty = 0 WHERE product_id = " . intval($this->_getRequest()->getPost('pID')) . " AND shake_id = 2;";
                            $writeConnection->query($query1);                            
                            $shakes_id = "";
                            $no_of_shakes = 0;
                            $current_hmr_product_shakes_result = $readConnection->fetchAll('SELECT * FROM custom_hmr_product_shakes WHERE product_id = ' . intval($this->_getRequest()->getPost('pID'))); 
                            if(count($current_hmr_product_shakes_result) > 0 ){
                                foreach( $current_hmr_product_shakes_result as $hmr_product_shake) {          
                                  $shakes_id .= $hmr_product_shake['shake_id'].",";
                                  $no_of_shakes = $no_of_shakes + 1;
                                }
                            }                           
                            $query = "UPDATE custom_hmr_product_kit SET
                                         no_of_shakes = '". $no_of_shakes . "',
                                         shakes_id = '" . $shakes_id . "'                                           
                                         WHERE product_id = " . intval($this->_getRequest()->getPost('pID')) . ";";
                            $writeConnection->query($query);                            
                         }

                         // Variety of Entrees
                         //echo "SELECT * FROM `custom_hmr_product_entrees_values` WHERE `product_id` = ".intval($this->_getRequest()->getPost('pID'));exit;
                         if($this->_getRequest()->getPost('chk_choose_variety') == 1) {
                         $hmr_product_variety = $readConnection->fetchAll('SELECT * FROM `custom_hmr_product_entrees_values` WHERE `product_id` = ' . intval($this->_getRequest()->getPost('pID')));
                            
                            $entrees_ar = '';

                            if(count($hmr_product_variety) > 0 ){

                                $delvariety = "DELETE FROM custom_hmr_product_entrees_values WHERE product_id = " . intval($this->_getRequest()->getPost('pID'));
                                $writeConnection->query($delvariety);

                                for($i=1; $i<=15; $i++){

                                    if($this->_getRequest()->getPost('entree_'.$i)!=0) {


                                        $entrees_ar .= $this->_getRequest()->getPost('chk_entree_'.$i).","; 

                                        if($i==15) {

                                            $entrees_ar = rtrim($entrees_ar, ",");
                                        
                                        
                                        }

                                    } 

                                    if($this->_getRequest()->getPost('entree_'.$i)!=0){

                                         $queryforvariety = "INSERT INTO  `custom_hmr_product_entrees_values` (`product_id`, `entree_id`, `default_qty`, `status`) VALUES (
                                         ". intval($this->_getRequest()->getPost('pID')) .", ".$this->_getRequest()->getPost('chk_entree_'.$i).", ".$this->_getRequest()->getPost('entree_'.$i).", 1);";


                                        $writeConnection->query($queryforvariety);
                                    }
                                    else {

                                         $queryforvariety = "INSERT INTO  `custom_hmr_product_entrees_values` (`product_id`, `entree_id`, `default_qty`, `status`) VALUES (
                                         ". intval($this->_getRequest()->getPost('pID')) .", '".$i."' , ".$this->_getRequest()->getPost('entree_'.$i).", 1);";
                                        

                                        $writeConnection->query($queryforvariety);
                                    }

                                }
                                    
                                $query = "UPDATE `custom_hmr_product_kit` SET
                                         `entrees_id` = '". $entrees_ar . "',
                                         `no_of_entrees` = '".$this->_getRequest()->getPost('hmr_tot_entrees_qty')."'
                                         WHERE `product_id` = " . intval($this->_getRequest()->getPost('pID')) . ";";

                                        $writeConnection->query($query);

                                
                                
                            }else {

                                    for($i=1; $i<=15; $i++){

                                    if($this->_getRequest()->getPost('entree_'.$i)) {


                                         $entrees_ar .= $this->_getRequest()->getPost('chk_entree_'.$i).",";

                                        if($i==15) {

                                            $entrees_ar = rtrim($entrees_ar, ",");
                                        
                                        }   

                                    }                                   

                                    if($this->_getRequest()->getPost('entree_'.$i)!=0){

                                        $queryforvariety = "INSERT INTO  `custom_hmr_product_entrees_values` (`product_id`, `entree_id`, `default_qty`, `status`) VALUES (
                                         ". intval($this->_getRequest()->getPost('pID')) .", ".$this->_getRequest()->getPost('chk_entree_'.$i).", ".$this->_getRequest()->getPost('entree_'.$i).", 1);";
                                        

                                        $writeConnection->query($queryforvariety);
                                    }
                                    else {
                                        $queryforvariety = "INSERT INTO  `custom_hmr_product_entrees_values` (`product_id`, `entree_id`, `default_qty`, `status`) VALUES (
                                         ". intval($this->_getRequest()->getPost('pID')) .", ".$i.", ".$this->_getRequest()->getPost('entree_'.$i).", 1);";
                                        $writeConnection->query($queryforvariety);
                                    }


                                    }   

                                    $query = "UPDATE `custom_hmr_product_kit` SET
                                         `entrees_id` = '". $entrees_ar . "',
                                         `no_of_entrees` = '".$this->_getRequest()->getPost('hmr_tot_entrees_qty')."'                                   
                                         WHERE `product_id` = " . intval($this->_getRequest()->getPost('pID')) . ";";

                                        $writeConnection->query($query);
                                    
                                //} 
                                    
                                }
                            }

                         //next_subscription_product
                         if($this->_getRequest()->getPost('next_subscription_product') != 0){
                            $hmr_product_next_result = $readConnection->fetchAll('SELECT * FROM `custom_hmr_product_2_week_product` WHERE product_id = ' . intval($this->_getRequest()->getPost('pID')));
                            if(count($hmr_product_next_result) > 0 ){
                                $query = "UPDATE `custom_hmr_product_2_week_product` SET
                                         `second_week_product_id` = '" . intval($this->_getRequest()->getPost('next_subscription_product')) . "'                                            
                                         WHERE product_id = " . intval($this->_getRequest()->getPost('pID')) . ";";
                                $writeConnection->query($query);
                            }else{                              
                                $query = "INSERT INTO `custom_hmr_product_2_week_product` (product_id, second_week_product_id, status) VALUES (
                                         " . intval($this->_getRequest()->getPost('pID')) . ", " .intval($this->_getRequest()->getPost('next_subscription_product')) . ", 1);";
                                $writeConnection->query($query);

                            }
                         }
                    } 
                }
                $product->save();
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }
    public function salesConvertQuoteItemToOrderItem(Varien_Event_Observer $observer)
    {
        $quoteItem = $observer->getItem();
        if ($additionalOptions = $quoteItem->getOptionByCode('additional_options')) {
            $orderItem = $observer->getOrderItem();
            $options = $orderItem->getProductOptions();
            $options['additional_options'] = unserialize($additionalOptions->getValue());
            $orderItem->setProductOptions($options);
        }
    }
    public function salesQuoteAddressCollectTotalsBefore(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $quote_items = $quote->getItemsCollection();
		$resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $i = 0;
        $quote_array = $quote_items->getData();
		$allEntrees_result = $readConnection->fetchAll('SELECT * FROM custom_hmr_entrees');
		foreach ($quote_items as $item) {
			$_product_id = $quote_array[$i]['product_id'];
			$_current_product = Mage::getModel('catalog/product')->load($_product_id);
			if($_current_product->getHmrProduct()== 1){
				if($quote_array[$i]['subscribed_prod_id']!='0' && $quote_array[$i]['variety_entrees'] != '') {
					$hmr_product_specs = '<table width="100%">';
					$hmr_product_specs .= '<tr><td>';
					$hmr_product_specs .= '<div>';
					$hmr_product_specs .= '<span class="phase_1_title_1" style="display: block;">';
					$hmr_product_specs .= '<strong>Initial Kit Contents:</strong></span>';
					if($quote_array[$i]['variety_entrees'] != ''){
						$variety_entrees = explode(",",$quote_array[$i]['variety_entrees']);
						foreach($allEntrees_result as $key=>$value){ // go through this loop for the 3 week products to ship
							if($variety_entrees[$key]!=0) {
								$hmr_product_specs.= '<dt>'.$variety_entrees[$key].'<span class="smallText">&nbsp;X&nbsp;</span>'.$value['entree_name'].'</dt>';
							}
						}
					}
                    if ($_product_id == '131')
                    {
                        $hmr_product_specs .= '<dt>1<span class="smallText">&nbsp;X&nbsp;</span>box of HMR Multigrain hot cereal</dt>';
                    }
					if($quote_array[$i]['hmr_120_choco'] > 0){
						$hmr_product_specs .= '<dt>'.$quote_array[$i]['hmr_120_choco'].'<span class="smallText">&nbsp;X&nbsp;</span>HMR&reg; 120 Chocolate</dt>';
					}
					if($quote_array[$i]['hmr_120_vani'] > 0){
						$hmr_product_specs .= '<dt>'.$quote_array[$i]['hmr_120_vani'].'<span class="smallText">&nbsp;X&nbsp;</span>HMR&reg; 120 Vanilla</dt>';
					}
					if($quote_array[$i]['hrm_70_choco'] > 0){
						$hmr_product_specs .= '<dt>'.$quote_array[$i]['hrm_70_choco'].'<span class="smallText">&nbsp;X&nbsp;</span>HMR&reg; 70 Plus Chocolate</dt>';
					}
					if($quote_array[$i]['hmr_70_vani'] > 0){
						$hmr_product_specs .= '<dt>'.$quote_array[$i]['hmr_70_vani'].'<span class="smallText">&nbsp;X&nbsp;</span>HMR&reg; 70 Plus Vanilla</dt>';
					}
					$hmr_product_specs.='</div></td></tr>';
					$hmr_product_specs.= '</table>';
				}
				if($quote_array[$i]['subscribed_prod_id'] =='0' && $quote_array[$i]['subscribed_variety_entree'] != '') {
					$hmr_product_specs  = '<table width="100%">';
					$hmr_product_specs .= '<tr><td>';
					$hmr_product_specs .= '<span class="phase_1_title_1" style="display: block;"><strong>Recurring Kit Contents:</strong></span>';
					$hmr_product_specs .= '<div>';
					$subcription_variety_entrees = explode(",",$quote_array[$i]['subscribed_variety_entree']);
					foreach($allEntrees_result as $key=>$value){ // loop through again this time for the 2 week products to ship.
						 if($subcription_variety_entrees[$key]!=0){
							$hmr_product_specs.= '<dt>'.$subcription_variety_entrees[$key].'<span class="smallText">&nbsp;X&nbsp;</span>'.$value['entree_name'].'</dt>';
						}
					}
					if( $quote_array[$i]['subscribed_hmr_120_choco'] > 0){
						$hmr_product_specs .= '<dt>'.$quote_array[$i]['subscribed_hmr_120_choco'].'<span class="smallText">&nbsp;X&nbsp;</span>HMR&reg; 120 Chocolate</dt>';
					}
					if($quote_array[$i]['subscribed_hmr_120_vani'] > 0){
						$hmr_product_specs .= '<dt>'.$quote_array[$i]['subscribed_hmr_120_vani'].'<span class="smallText">&nbsp;X&nbsp;</span>HMR&reg; 120 Vanilla</dt>';
					}
					if($quote_array[$i]['subscribed_hmr_70_choco'] > 0){
						$hmr_product_specs .= '<dt>'.$quote_array[$i]['subscribed_hmr_70_choco'].'<span class="smallText">&nbsp;X&nbsp;</span>HMR&reg; 70 Plus Chocolate</dt>';
					}
					if($quote_array[$i]['subscribed_hmr_70_vani'] > 0){
						$hmr_product_specs .= '<dt>'.$quote_array[$i]['subscribed_hmr_70_vani'].'<span class="smallText">&nbsp;X&nbsp;</span>HMR&reg; 70 Plus Vanilla</dt>';
					}

                    $hmr_product_specs .= '</div>';
					$hmr_product_specs .= '</td></tr>';
					$hmr_product_specs .= '</table>';
				}
                $additionalOptions = array(array(
                    'code' => 'hmr_spec',
                    'label' => '',
                    'value' => $hmr_product_specs
                ));
                $item->addOption(array(
                    'code' => 'additional_options',
                    'value' => serialize($additionalOptions),
                ));
            }
            $i++;
        }
    }
    public function salesQuoteRemoveItem(Varien_Event_Observer $observer){

    }
	public function checkKitDetails(Varien_Event_Observer $observer)
	{
		// Get cart helper
		$cart = Mage::helper('checkout/cart')->getCart();

		// Get all items from the quote
		$cart_items = $cart->getItems();

		// Array for holding cart item ids.
		$cartIds = array();

		// For each item in the cart...
		foreach($cart_items as &$item)
		{
			$cartIds[] = $item->getProductId();
		}

		// Unset for each reference
		unset($item);

		// Set flags to only allow one of each type of kit.
		$firstIntroSeen = false;
		$firstRecurSeen = false;
		$itemDeleted = false;

		// Loop through those items.
		foreach ($cart_items as &$item)
		{
			// Init a flag for deleting the item if needed
			$itemDeleteFlag = false;

			// Get all additional options for this item.
			$quote_item = Mage::getModel('sales/quote_item')->setProduct($item);

			// Load in product details.
			$itemDetails = $quote_item->getProduct()->getData();

			// If this item's product_id is a intro kit....
			if(in_array($item['product_id'], $this->kitIds))
			{
				// If this is an intro kit and we've already seen an intro kit...
				if($firstIntroSeen === true)
				{
					// Set this item to be removed.
					$itemDeleteFlag = true;
				}

				// Mark intro as seen.
				$firstIntroSeen = true;

				// Gather entrees and shakes.
				$entrees = explode(',',$itemDetails['variety_entrees']);
				$shakes[] = $itemDetails['hmr_120_choco'];
				$shakes[] = $itemDetails['hmr_120_vani'];
				$shakes[] = $itemDetails['hrm_70_choco'];
				$shakes[] = $itemDetails['hmr_70_vani'];

				// If there are 0 entrees or 0 shakes. OR there are no recurring kits in the cart.
				if(array_sum($entrees) < 1 || array_sum($shakes) < 1 || count(array_intersect($cartIds, $this->recurringKitIds)) < 1)
				{
					// Set this item to be removed.
					$itemDeleteFlag = true;
				}

				// Unset arrays for memory.
				unset($shakes);
				unset($entrees);
			}

			// If this item's product id is a recurring kit...
			elseif(in_array($item['product_id'], $this->recurringKitIds))
			{
				// If this is a recurring kit and we've already seen a recurring kit...
				if($firstRecurSeen === true)
				{
					// Set this item to be removed.
					$itemDeleteFlag = true;
				}

				// Mark intro as seen.
				$firstRecurSeen = true;

				// Gather entrees and shakes.
				$entrees = explode(',',$itemDetails['subscribed_variety_entree']);
				$shakes[] = $itemDetails['subscribed_hmr_120_choco'];
				$shakes[] = $itemDetails['subscribed_hmr_120_vani'];
				$shakes[] = $itemDetails['subscribed_hmr_70_choco'];
				$shakes[] = $itemDetails['subscribed_hmr_70_vani'];

				// If there are 0 entrees or 0 shakes. OR there are no intro kits in the cart.
				if(array_sum($entrees) < 1 || array_sum($shakes) < 1 || count(array_intersect($cartIds, $this->kitIds)) < 1)
				{
					// Set this item to be removed.
					$itemDeleteFlag = true;
				}

				// Unset arrays for memory.
				unset($shakes);
				unset($entrees);
			}

			// If we are to remove this item from the cart...
			if($itemDeleteFlag === true)
			{
				// Remove item from cart by item id
				$cart->removeItem($item->getItemId());
				$itemDeleted = true;
			}
		}

		// Unset for each reference
		unset($item);

		// If we are to delete an item...
		if($itemDeleted === true)
		{
				// Prevent action because this recurring kit does not have an intro kit.
				$this->blockAddToCart('The details of your kit were unable to save. Please contact customer service to continue placing your order to your specification at 1-800-521-9054. We apologize for your inconvenience.', false);
		}
	}
	public function noDuplicateKits(Varien_Event_Observer $observer)
	{
			// Init cart ID array
			$idsInCart = array();

			//Get the product ID of the product being added.
			$productId = $observer->getProduct()->getId(); //Products being added

			//Set Kit IDs
			$kitIds = $this->kitIds;

			//Set Recurring Kit IDs
			$recurringKitIds = $this->recurringKitIds;

			//Get all items in the cart.
			$cartItems = Mage::helper('checkout/cart')->getCart()->getItems(); //Products in cart

			//For each product in cart...
			foreach($cartItems as &$item)
			{
				if(isset($idsInCart[$item->getProduct()->getId()]))
				{
					// Set this array item equal to it's current quantity plus this iteration's quantity.
					$idsInCart[$item->getProduct()->getId()] = intval($idsInCart[$item->getProduct()->getId()])+intval($item->getQty());
				}
				else
				{
					// Add this product's id to the array of product ids. Start qty.
					$idsInCart[$item->getProduct()->getId()] = $item->getQty();
				}

				// If this item is a kit or recurring kit and quantity is greater than 1
				if(in_array($item->getProduct()->getId(), array_merge($kitIds, $recurringKitIds)) && $item->getQty() > 1)
				{
					// Prevent action because there are duplicate kits.
					$this->blockAddToCart('The details of your kit were unable to save. Please contact customer service to continue placing your order to your specification at 1-800-521-9054. We apologize for your inconvenience.');
				}
			}

			// Unset for each reference
			unset($item);

			// ** Recurring kit must have an intro kit.
			// If this item is a recurring kit...
			if(in_array($productId, $recurringKitIds))
			{
				$introKitsInCart = array_intersect(array_keys($idsInCart), $kitIds);
				// And there is not an intro kit in the cart.
				if(count($introKitsInCart) > 0)
				{
					// Get the quantity of intro kit in cart and make sure it's 1. If it's not....
					if($idsInCart[$introKitsInCart[0]] !== 1)
					{
						// Prevent action because this recurring kit does not have an intro kit.
						$this->blockAddToCart('The details of your kit were unable to save. Please contact customer service to continue placing your order to your specification at 1-800-521-9054. We apologize for your inconvenience.');
					}
				}
			}

			unset($idsInCart);
			unset($cartItems);
			unset($introKitsInCart);
	}
	
	public function blockAddToCart($message, $redirect = TRUE)
	{
			//set error message in session
			Mage::getSingleton('core/session')->addError($message);

			if($redirect === TRUE)
			{
				//get URL model for cart/index
				$url = Mage::getModel('core/url')->getUrl('checkout/cart/index');

				//set redirect
				Mage::app()->getResponse()->setRedirect($url);

				//send redirect
				Mage::app()->getResponse()->sendResponse();

				//block further action
				exit;
			}
	}
	
	public function recurringProfileBilled($observer)
	{
		Mage::log('Hackley Kit Recurring Profile Billed Observer', null, 'my.log');
		Mage::log(1000, null, 'my.log');

		//$order = $observer->getOrder();
		//$profile = $observer->getProfile();

		//Mage::log($order->getData(), null, 'my.log');
		//Mage::log($profile->getData(), null, 'my.log');

//		$resource = Mage::getSingleton('core/resource');
//		$readConnection = $resource->getConnection('core_read');
//		$writeConnection = $resource->getConnection('core_write');
//
//		$hmr_product_specs = '';
//		$recurring_payment_id = $this->getRequestData('recurring_payment_id');
//		$reurring_profile_details = $readConnection->fetchall("select * from `sales_recurring_profile` where `reference_id` = '".$recurring_payment_id."'");
//		$order_item_info = unserialize($reurring_profile_details[0]['order_item_info']);
//		$getQuoteItemId = $order_item_info['item_id'];
//
//		$allEntrees_result = $readConnection->fetchAll('SELECT * FROM custom_hmr_entrees');
//		$quote_array =  $readConnection->fetchAll('SELECT * FROM `sales_custom_flat_quote_item` WHERE `item_id`='.$getQuoteItemId);
//
//		$hmr_product_specs  = '<table><tbody>';
//		$hmr_product_specs .= '<tr bgcolor="#FFFFFF" class="attributes-odd"><td>';
//		$hmr_product_specs .= '<strong>['.$quote_array[0]['subscribed_prod_ref'].']</strong>';
//		$hmr_product_specs .= '<div>';
//
//		$allsubcribedproductoptions = explode(",", $quote_array[0]['subscribed_variety_entree']);
//		foreach($allEntrees_result as $key=>$value){
//			if($allsubcribedproductoptions[$key]!=0){
//				$hmr_product_specs.=$allsubcribedproductoptions[$key].'<span class="smallText">X</span>'.$value['entree_name'].'<br>';
//			}
//		}
//		if($quote_array[0]['hmr_120_cho_qty'] > 0){
//			$hmr_product_specs .= $quote_array[0]['hmr_120_cho_qty'].'<span class="smallText"> X </span> HMR&reg; 120 Chocolate<br />';
//		}
//		if($quote_array[0]['hmr_120_van_qty'] > 0){
//			$hmr_product_specs .= $quote_array[0]['hmr_120_van_qty'].'<span class="smallText"> X </span> HMR&reg; 120 Vanilla<br />';
//		}
//		$hmr_product_specs.= '</div></td></tr>';
//		$hmr_product_specs.= '</tbody></table>';
//
//		$options['additional_options'] = array(
//			array(
//				'code' => 'hmr_spec',
//				'label' => '',
//				'value' => $hmr_product_specs
//			));
//		$additionalOptions = serialize($options);
//
//		$query = "UPDATE `sales_flat_order_item` SET `product_options` = '".$additionalOptions."' WHERE `order_id` = '".$order->getId()."'";
//		$writeConnection->query($query);
	}


    public function paymentMethodIsActive(Varien_Event_Observer $observer)
    {
        // Get event!
        $event = $observer->getEvent();

        // Get payment method!
        $method = $event->getMethodInstance();
        $code   = substr($method->getCode(), 0, 6);

        // If this payment method's code is paypal, begin processing!
        if ( $code === 'paypal' || $code === 'payflo' )
        {
            // Load necessary vars from the event & quote
            $result = $event->getResult();
            $items  = $event->getQuote()->getAllVisibleItems();

            // Check each item in the quote
            foreach ($items as $item)
            {
                // If this product is recurring it would trigger a recurring profile....
                if ( $item->getProduct()->getIsRecurring() )
                {
                    // So disable this paypal method!
                    $result->isAvailable = false;
                    return false;
                }
            }
        }

        // Otherwise this gateway is fine!
        return true;
    }

    /**
     * Retrieve the product model
     *
     * @return Mage_Catalog_Model_Product $product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }
     
    /**
     * Shortcut to getRequest
     *
     */
    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }
}