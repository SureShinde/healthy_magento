<?php
class Capitaln_Hacklehealthmanagement_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
	    $this->loadLayout();
	    $this->renderLayout();
    }

    public function editorderAction()
    {
         $this->loadLayout();
         $this->renderLayout();
    }

	public function hrmAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}

	/*
	===============
	This functon is used to update the recurring kit
	for a user after the order is complete. Thay may
	do this from their HMR-At-Home Dashboard.
	===============
	*/
	public function updateSubcriptionProductOptionAction()
	{
		//Get database resources
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$writeConnection = $resource->getConnection('core_write');

		//Gather necessary information
		$allEntrees_result = $readConnection->fetchAll('SELECT * FROM custom_hmr_entrees'); //Get all entrees
		$getQuoteItemId = $_POST['item_id']; //Get quote item's id
		$quote_array =  $readConnection->fetchAll('SELECT * FROM `sales_custom_flat_quote_item` WHERE `item_id`='.$getQuoteItemId); //Get quote item from DB
		$quoteItem = Mage::getModel("sales/quote_item")->load($getQuoteItemId);

		//Build & Save HTML!
		$hmrDesc = $this->buildDescriptionHTML($allEntrees_result, $getQuoteItemId, $quote_array);
		$additionalOptions = array(array(
			'code' => 'hmr_spec',
			'label' => '',
			'value' => $hmrDesc
		));
		$quoteItem->addOption(array(
			'code' => 'additional_options',
			'value' => serialize($additionalOptions),
		))->save();

		//Build final version of all subscription product options.
		$allsubcribedproductoptions = '';
		foreach($allEntrees_result as $key=>$value){
			if($_POST['hmr_entree_'.($key+1).'_qty']>0){
				$allsubcribedproductoptions .= ','.$_POST['hmr_entree_'.($key+1).'_qty'];
			}else{
				$allsubcribedproductoptions .= ',0';
			}
		}
		$allsubcribedproductoptions_final = ltrim($allsubcribedproductoptions, ',');

		//Build & Execute update query.
		$query_to_update_shakes = "UPDATE
			`sales_custom_flat_quote_item` SET
				`subscribed_hmr_70_choco` = '".$_POST['hmr_70_cho_qty']."',
				`subscribed_hmr_70_vani` = '".$_POST['hmr_70_van_qty']."',
				`subscribed_hmr_120_choco` = '".$_POST['hmr_120_cho_qty']."',
				`subscribed_hmr_120_vani` = '".$_POST['hmr_120_van_qty']."',
				`subscribed_variety_entree` = '".$allsubcribedproductoptions_final."'
			WHERE `item_id` = '".$getQuoteItemId."'";
		$writeConnection->query($query_to_update_shakes);

		//Redirect the user back to their kit dashboard.
		$this->_redirect('sales/recurring_profile/');
	}

	/*
	===============
	This functon is used to build the description
	for the recurring kit after editing it from
	your dashboard.
	===============
	*/
	public function buildDescriptionHTML($allEntrees_result, $getQuoteItemId, $quote_array){
		$hmr_product_specs  = '<table><tbody>';
		$hmr_product_specs .= '<tr bgcolor="#FFFFFF" class="attributes-odd"><td>';
		$hmr_product_specs .= '<strong>['.$quote_array[0]['subscribed_prod_ref'].']</strong>';
		$hmr_product_specs .= '<div>';
		$allsubcribedproductoptions = '';
		foreach($allEntrees_result as $key=>$value){
			$hmr_product_specs.= '<dt>'.$_POST['hmr_entree_'.($key+1).'_qty'].'<span class="smallText">&nbsp;X&nbsp;</span>'.$value['entree_name'].'</dt>';
		}
		if($_POST['hmr_120_cho_qty'] > 0){
			$hmr_product_specs .= '<dt>'.$_POST['hmr_120_cho_qty'].'<span class="smallText">&nbsp;X&nbsp;</span>HMR&reg; 120 Chocolate</dt>';
		}
		if($_POST['hmr_120_van_qty'] > 0){
			$hmr_product_specs .= '<dt>'.$_POST['hmr_120_van_qty'].'<span class="smallText">&nbsp;X&nbsp;</span>HMR&reg; 120 Vanilla</dt>';
		}
		if($_POST['hmr_70_cho_qty'] > 0){
			$hmr_product_specs .= '<dt>'.$_POST['hmr_70_cho_qty'].'<span class="smallText">&nbsp;X&nbsp;</span>HMR&reg; 70 Chocolate</dt>';
		}
		if($_POST['hmr_70_van_qty'] > 0){
			$hmr_product_specs .= '<dt>'.$_POST['hmr_70_van_qty'].'<span class="smallText">&nbsp;X&nbsp;</span>HMR&reg; 70 Vanilla</dt>';
		}
		$hmr_product_specs.= '</div></td></tr>';
		$hmr_product_specs.= '</tbody></table>';
		return $hmr_product_specs;
	}

	/*
	===============
	This functon is run when someone pauses an active kit.
	===============
	*/
    public function updatestatuspauseAction()
    {
        $custId = Mage::getSingleton('customer/session')->getId();
        $orderId = Mage::app()->getRequest()->getParam('order_id');
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');    
        $writeConnection = $resource->getConnection('core_write');
        $getQuoteItemId = $readConnection->fetchOne('SELECT `quote_item_id` FROM `sales_flat_order_item` WHERE `order_id` = '  .$orderId);
        $query_to_update_status = "UPDATE `custom_sales_flat_quote_item` SET `action` = '0'  WHERE `item_id` = '".$getQuoteItemId."'";
        $writeConnection->query($query_to_update_status);
        $this->_redirect('hacklehealthmanagement/index/index/order_id/'.$custId);
    }

	/*
	===============
	This functon is run when someone resumes a paused a kit.
	===============
	*/
    public function updatestatusresumeAction()
    {
        $custId = Mage::getSingleton('customer/session')->getId();
        $orderId = Mage::app()->getRequest()->getParam('order_id');
        $date = Mage::app()->getRequest()->getParam('date');
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');    
        $writeConnection = $resource->getConnection('core_write');
        $getQuoteItemId = $readConnection->fetchOne('SELECT `quote_item_id` FROM `sales_flat_order_item` WHERE `order_id` = '  .$orderId);
        $query_to_update_status = "UPDATE `custom_sales_flat_quote_item` SET `action` = '1',`nextshipdate` = '".$date."'   WHERE `item_id` = '".$getQuoteItemId."'";
        $writeConnection->query( $query_to_update_status);
        $this->_redirect('hacklehealthmanagement/index/index/order_id/'.$custId);
    }

    public function editRecurringProfileAction()
    {
		$resource = Mage::getSingleton( 'core/resource' );
		$read = $resource->getConnection( 'core_read' );
		$write = $resource->getConnection( 'core_write' );
		$table = 'sales_recurring_profile';

		// Get the profile id.
		$id = Mage::app()->getRequest()->getParam( 'profile' );

		if( $id )
		{
			// Get additional info for this profile.
			$additionalGetQuery = 'SELECT additional_info FROM ' . $table . ' WHERE profile_id = ' . (int) $id . ' LIMIT 1';

			// Get Result
			$result = $read->fetchOne($additionalGetQuery);

			// If we have a result
			if( $result )
			{
				// Unserialize to operate.
				$additional = unserialize($result);

				// If it unserialized successfully and has results
				if ( ! empty( $additional ) )
				{
					// Set next_cycle
					$additional['next_cycle'] = strtotime(Mage::app()->getRequest()->getParam('next_billed')) + ( 4 * 60 * 60 );

					// If next cycle is set
					if ( is_numeric($additional['next_cycle']) && $additional['next_cycle'] !== 0 )
					{
						// Revert to serialized string.
						$additional = serialize($additional);

						// Save new additional string to db.
						$additionalSetQuery = 'UPDATE ' . $table . ' SET additional_info = :additional WHERE profile_id = :id';
						$additionalSetVars  = array( 'additional' => $additional, 'id' => (int) $id );

						// Update DB.
						$write->query($additionalSetQuery, $additionalSetVars);
					}
				}
			}
		}

		// Redirect back to grid.
        $this->_redirect('sales/recurring_profile');
    }
  }